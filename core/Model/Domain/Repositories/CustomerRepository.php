<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CustomerMapper;
use Alpha\Model\Domain\Entities\Customer\Customer;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\EntityHydrator;
use Alpha\Support\AlphaString;

/**
 * Class CustomerRepository
 * 
 * Camada de domínio para a gestão de clientes.
 * Centraliza a lógica de autenticação (password_verify), controle de 
 * força bruta (login attempts) e abstrai as operações do CustomerMapper.
 */
class CustomerRepository extends AbstractRepository implements BaseRepositoryInterface
{

    private ?array $configSettings = null;

    private function getConfigValue(string $key, mixed $default = null): mixed
    {
        if ($this->config) {
            return $this->config->get($key) ?? $default;
        }

        if ($this->configSettings === null) {
            try {
                $settingRepo = RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\SettingRepository::class);
                $this->configSettings = $settingRepo->getSetting('config', 1);
            } catch (\Throwable) {
                $this->configSettings = [];
            }
        }
        return $this->configSettings[$key] ?? $default;
    }

    private function getTranslation(string $key, string $route, string $default = ''): string
    {
        $langData = $this->loadLanguage($route);
        if (isset($langData[$key])) {
            return (string)$langData[$key];
        }

        return $default;
    }

    protected function getMapper(): CustomerMapper
    {
        return $this->mapperFactory->get(CustomerMapper::class);
    }

    public function find(int $id): ?Customer
    {
        $customer = $this->getMapper()->findById($id);
        if (!$customer) {
            return null;
        }

        $storeId = $this->store_id;
        if ($storeId > 0 && $customer->getStoreId() !== $storeId) {
            return null;
        }

        return $customer;
    }

    /**
     * Busca a entidade de um cliente através do seu e-mail.
     */
    public function findByEmail(string $email): ?Customer
    {
        $criteria = ['email' => $email];
        $storeId = $this->store_id;
        if ($storeId > 0) {
            $criteria['store_id'] = $storeId;
        }

        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }

    /**
     * Autentica um cliente verificando a senha com password_verify nativo.
     * Retorna a entidade do cliente se a senha for válida, null caso contrário.
     */
    public function authenticate(string $email, string $password): ?Customer
    {
        $customer = $this->findByEmail($email);

        if (!$customer) {
            return null;
        }

        $storedHash = $customer->getPassword();

        // Alpha Engine: Validação moderna com fallback seguro.
        // A migração de senhas legadas (salt) para password_hash nativo
        // deve ocorrer progressivamente durante o login do usuário.
        if (password_verify($password, $storedHash)) {

            // Se a senha precisar de rehash (ex: mudança nas opções de custo ou migração legado)
            if (password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
                $this->updatePassword($customer->getId(), $password);
            }

            return $customer;
        }

        // Tratamento para senhas antigasdo código legado (SHA1 com salt)
        // pode ser injetado aqui via mapper caso necessário.

        return null;
    }

    /**
     * Atualiza a senha do cliente já convertendo para o hash moderno.
     */
    public function updatePassword(int $customerId, string $rawPassword): void
    {
        $customer = $this->find($customerId);
        if ($customer) {
            $customer->setPassword(password_hash($rawPassword, PASSWORD_DEFAULT));
            $this->getMapper()->update($customer);
        }
    }

    /**
     * Verifica se a conta do cliente está temporariamente bloqueada (Força Bruta).
     */
    public function isLockedOut(string $email, int $maxAttempts): bool
    {
        return $this->getMapper()->getLoginAttempts($email) >= $maxAttempts;
    }

    /**
     * Registra uma tentativa de login falha.
     */
    public function addLoginAttempt(string $email, string $ip = ''): void
    {
        $this->getMapper()->addLoginAttempt($email, $ip);
    }

    /**
     * Reseta (limpa) as tentativas de login após uma autenticação bem-sucedida.
     */
    public function resetLoginAttempts(string $email): void
    {
        $this->getMapper()->deleteLoginAttempts($email);
    }

    /**
     * Valida e registra um novo cliente encapsulando regras de negócio e infraestrutura.
     * 
     * @param array $data Dados vindos do formulário (POST)
     * @return array Array contendo status da transação, erros e ID do cliente.
     */
    public function registerCustomer(array $data): array
    {
        // Inicialização de fallbacks resilientes para rodar tantono código legado quanto no Slim standalone
        $storeId = $this->getStoreId();
        $languageId = (int)$this->getConfigValue('config_language_id', 2);
        $defaultGroupId = (int)$this->getConfigValue('config_customer_group_id', 1);

        // Definição de Grupo de Clientes
        $customer_group_id = !empty($data['customer_group_id']) ? (int)$data['customer_group_id'] : $defaultGroupId;

        $customerGroupRepo = null;
        if ($this->container && $this->container->has(RepositoryFactory::class)) {
            $customerGroupRepo = $this->container->get(RepositoryFactory::class)->get(CustomerGroupRepository::class);
        } else {
            try {
                $customerGroupRepo = RepositoryFactory::getInstance()->get(CustomerGroupRepository::class);
            } catch (\Throwable) {
                // Fallback silencioso
            }
        }

        $customer_group_info = $customerGroupRepo ? $customerGroupRepo->getCustomerGroup($customer_group_id, $languageId) : null;

        // Alpha Engine: Delegação da Validação DRY
        $errors = $this->validateRegistrationData($data, $customer_group_id, $customer_group_info);

        if ($errors) {
            return [
                'customer_id' => null,
                'errors' => $errors,
                'customer_group_info' => $customer_group_info,
                'customer_group_id' => $customer_group_id
            ];
        }

        // --- Hidratação da Entidade ---
        $data['customer_group_id'] = $customer_group_id;
        $data['ip'] = $data['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $data['token'] = '';
        $data['code'] = '';
        $data['date_added'] = date('Y-m-d H:i:s');

        $customer = new Customer();

        EntityHydrator::fillEntity($customer, $data);

        $password = html_entity_decode($data['password'] ?? '', ENT_QUOTES, 'UTF-8');
        $customer->setStoreId($storeId)
            ->setLanguageId($languageId)
            ->setPassword(password_hash($password, PASSWORD_DEFAULT))
            ->setStatus(true)
            ->setSafe(true);

        $customerId = $this->save($customer);

        return [
            'customer_id' => $customerId,
            'errors' => [],
            'customer_group_info' => $customer_group_info,
            'customer_group_id' => $customer_group_id
        ];
    }

    /**
     * Valida os dados de registro isolando o fluxo de erros de forma limpa.
     */
    public function validateRegistrationData(array $data, int $customerGroupId, ?array $customerGroupInfo): array
    {
        $errors = [];

        $telephoneRequired = (bool)$this->getConfigValue('config_telephone_required', false);
        $customerGroupDisplay = (array)$this->getConfigValue('config_customer_group_display', [1]);
        $accountId = (int)$this->getConfigValue('config_account_id', 5);

        $getLang = function (string $key, string $default = '') {
            return $this->getTranslation($key, 'account/register', $default);
        };

        if (!$customerGroupInfo || !in_array($customerGroupId, $customerGroupDisplay)) {
            $errors['warning'] = $getLang('error_customer_group', 'O grupo de clientes parece não ser válido!');
        }

        if (!AlphaString::validateLength($data['firstname'] ?? '', 1, 32)) {
            $errors['firstname'] = $getLang('error_firstname', 'O nome deve ter entre 1 e 32 caracteres.');
        }
        if (!AlphaString::validateLength($data['lastname'] ?? '', 1, 32)) {
            $errors['lastname'] = $getLang('error_lastname', 'O sobrenome deve ter entre 1 e 32 caracteres.');
        }
        if (!AlphaString::validateEmail($data['email'] ?? '')) {
            $errors['email'] = $getLang('error_email', 'O e-mail não é válido.');
        } elseif ($this->findByEmail($data['email'] ?? '')) {
            $errors['warning'] = $getLang('error_exists', 'Atenção: Este e-mail já está cadastrado.');
        }
        if ($telephoneRequired && !AlphaString::validateLength($data['telephone'] ?? '', 3, 32)) {
            $errors['telephone'] = $getLang('error_telephone', 'O telefone deve ter entre 10 e 11 números.');
        }

        // DRY: Validação Compartilhada - Documentos e Senha
        $docError = $this->getDocumentError($data['persontype'] ?? '', $data['cpf_cnpj'] ?? '');
        if ($docError) $errors['cpf_cnpj'] = $docError;

        $password = html_entity_decode($data['password'] ?? '', ENT_QUOTES, 'UTF-8');
        $passError = $this->getPasswordError($password);
        if ($passError) $errors['password'] = $passError;

        // Aceite dos Termos de Uso
        $informationRepo = null;
        if ($this->container && $this->container->has(RepositoryFactory::class)) {
            $informationRepo = $this->container->get(RepositoryFactory::class)->get(InformationRepository::class);
        } else {
            try {
                $informationRepo = RepositoryFactory::getInstance()->get(InformationRepository::class);
            } catch (\Throwable) {
            }
        }

        $information_info = $informationRepo ? $informationRepo->getInformation($accountId) : null;
        if ($information_info && empty($data['agree'])) {
            $errors['warning'] = sprintf($getLang('error_agree', 'Atenção: Você deve aceitar o contrato de %s.'), $information_info['title']);
        }

        return $errors;
    }

    /**
     * Atualiza o perfil completo do cliente através da Entidade.
     */
    public function updateProfile(Customer $customer): void
    {
        $this->getMapper()->update($customer);
    }

    /**
     * Validações de domínio para edição de conta do cliente.
     * @return array Array contendo os erros encontrados. Vazio se válido.
     */
    public function validateEditData(array $data, int $customerId): array
    {
        $errors = [];

        $getLang = function (string $key, string $default = '') {
            return $this->getTranslation($key, 'account/edit', $default);
        };

        if (!AlphaString::validateLength($data['firstname'] ?? '', 1, 32)) {
            $errors['firstname'] = $getLang('error_firstname', 'O nome deve ter entre 1 e 32 caracteres.');
        }
        if (!AlphaString::validateLength($data['lastname'] ?? '', 1, 32)) {
            $errors['lastname'] = $getLang('error_lastname', 'O sobrenome deve ter entre 1 e 32 caracteres.');
        }
        if (!AlphaString::validateEmail($data['email'] ?? '')) {
            $errors['email'] = $getLang('error_email', 'O e-mail não é válido.');
        }

        $existingCustomer = $this->findByEmail($data['email'] ?? '');
        if ($existingCustomer && $existingCustomer->getId() !== $customerId) {
            $errors['warning'] = $getLang('error_exists', 'Atenção: Este e-mail já está cadastrado.');
        }

        $telephoneRequired = (bool)$this->getConfigValue('config_telephone_required', false);
        if ($telephoneRequired && !AlphaString::validateLength($data['telephone'] ?? '', 3, 32)) {
            $errors['telephone'] = $getLang('error_telephone', 'O telefone deve ter entre 10 e 11 números.');
        }

        // DRY: Documentos
        $docError = $this->getDocumentError($data['persontype'] ?? '', $data['cpf_cnpj'] ?? '');
        if ($docError) $errors['cpf_cnpj'] = $docError;

        return $errors;
    }

    /**
     * Validações de domínio para alteração de senha.
     * @return array Array contendo os erros encontrados. Vazio se válido.
     */
    public function validatePasswordData(array $data): array
    {
        $errors = [];

        $getLang = function (string $key, string $default = '') {
            return $this->getTranslation($key, 'account/password', $default);
        };

        $password = html_entity_decode($data['password'] ?? '', ENT_QUOTES, 'UTF-8');

        $passError = $this->getPasswordError($password);
        if ($passError) $errors['password'] = $passError;

        if (($data['confirm'] ?? '') != $password) {
            $errors['confirm'] = $getLang('error_confirm', 'A confirmação de senha não coincide com a senha.');
        }

        return $errors;
    }

    /**
     * Helpers DRY para validações comuns de clientes.
     */
    private function getDocumentError(string $personType, string $cpfCnpj): ?string
    {
        $getLang = function (string $key, string $default = '') {
            return $this->getTranslation($key, 'account/register', $default);
        };

        if ($personType === 'F' && !empty($cpfCnpj)) {
            $cpf = preg_replace('/[^0-9]/', '', (string)$cpfCnpj);
            if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
                return $getLang('error_cpf', 'O CPF informado é inválido.');
            }
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += (int)$cpf[$c] * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ((int)$cpf[$c] !== $d) {
                    return $getLang('error_cpf', 'O CPF informado é inválido.');
                }
            }
        } elseif ($personType === 'J' && !empty($cpfCnpj)) {
            $cnpj = preg_replace('/[^0-9]/', '', (string)$cpfCnpj);
            if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
                return $getLang('error_cnpj', 'O CNPJ informado é inválido.');
            }
            $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
            for ($i = 0, $n = 0; $i < 12; $n += (int)$cnpj[$i] * $b[++$i]);
            if ((int)$cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) return $getLang('error_cnpj', 'O CNPJ informado é inválido.');
            for ($i = 0, $n = 0; $i <= 12; $n += (int)$cnpj[$i] * $b[$i++]);
            if ((int)$cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) return $getLang('error_cnpj', 'O CNPJ informado é inválido.');
        }
        return null;
    }

    private function getPasswordError(string $password): ?string
    {
        $getLang = function (string $key, string $default = '') {
            return $this->getTranslation($key, 'account/register', $default);
        };

        $passwordLength = (int)$this->getConfigValue('config_password_length', 4);

        if (!AlphaString::validateLength($password, $passwordLength, 40)) {
            return sprintf($getLang('error_password_length', 'A senha deve ter entre %d e 40 caracteres.'), $passwordLength);
        }

        $required = [];
        if ($this->getConfigValue('config_password_uppercase', false) && !preg_match('/[A-Z]/', $password)) {
            $required[] = $getLang('error_password_uppercase', 'letra maiúscula');
        }
        if ($this->getConfigValue('config_password_lowercase', false) && !preg_match('/[a-z]/', $password)) {
            $required[] = $getLang('error_password_lowercase', 'letra minúscula');
        }
        if ($this->getConfigValue('config_password_number', false) && !preg_match('/[0-9]/', $password)) {
            $required[] = $getLang('error_password_number', 'número');
        }
        if ($this->getConfigValue('config_password_symbol', false) && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $required[] = $getLang('error_password_symbol', 'caractere especial');
        }

        if ($required) {
            return sprintf($getLang('error_password', 'A senha deve conter pelo menos: %s.'), implode(', ', $required));
        }

        return null;
    }

    /**
     * Persiste (Salva) um novo cliente na base de dados.
     * 
     * @param Customer $customer
     * @return int O ID do cliente inserido.
     */
    public function save(Customer $customer): ?int
    {
        $id = $this->getMapper()->save($customer);
        if (!$id) {
            throw new \RuntimeException("Alpha Engine: Falha de Persistência. O DAO retornou nulo ao tentar salvar o Customer. Verifique o arquivo storage/logs/error.log para identificar qual coluna o banco de dados rejeitou (ex: restrição NOT NULL em colunas como 'ip' ou 'token').");
        }
        return $id;
    }

    // BaseRepositoryInterface bindings
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    /**
     * Busca a lista de transações do cliente.
     */
    public function getTransactions(int $customerId, int $start = 0, int $limit = 20): array
    {
        return $this->getMapper()->getTransactionsArray($customerId, $start, $limit);
    }

    /**
     * Retorna a contagem total de transações do cliente.
     */
    public function getTotalTransactions(int $customerId): int
    {
        return $this->getMapper()->getTotalTransactionsCount($customerId);
    }

    /**
     * Retorna o saldo total de transações do cliente.
     */
    public function getTransactionTotal(int $customerId): float
    {
        return $this->getMapper()->getTransactionTotalSum($customerId);
    }
}
