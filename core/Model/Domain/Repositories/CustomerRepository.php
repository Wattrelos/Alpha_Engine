<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CustomerMapper;
use Alpha\Model\Domain\Entities\Customer;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\DataTransferObject\EntityMapper;

/**
 * Class CustomerRepository
 * 
 * Camada de domínio para a gestão de clientes.
 * Centraliza a lógica de autenticação (password_verify), controle de 
 * força bruta (login attempts) e abstrai as operações do CustomerMapper.
 */
class CustomerRepository extends AbstractRepository implements BaseRepositoryInterface {

    protected function getMapper(): CustomerMapper {
        return $this->mapperFactory->get(CustomerMapper::class);
    }

    public function find(int $id): ?Customer {
        return $this->getMapper()->findById($id);
    }

    /**
     * Busca a entidade de um cliente através do seu e-mail.
     */
    public function findByEmail(string $email): ?Customer {
        $results = $this->getMapper()->search(['email' => $email]);
        return $results[0] ?? null;
    }

    /**
     * Autentica um cliente verificando a senha com password_verify nativo.
     * Retorna a entidade do cliente se a senha for válida, null caso contrário.
     */
    public function authenticate(string $email, string $password): ?Customer {
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
        
        // Tratamento para senhas antigas do OpenCart (SHA1 com salt)
        // pode ser injetado aqui via mapper caso necessário.

        return null;
    }

    /**
     * Atualiza a senha do cliente já convertendo para o hash moderno.
     */
    public function updatePassword(int $customerId, string $rawPassword): void {
        $customer = $this->find($customerId);
        if ($customer) {
            $customer->setPassword(password_hash($rawPassword, PASSWORD_DEFAULT));
            $this->getMapper()->update($customer);
        }
    }

    /**
     * Verifica se a conta do cliente está temporariamente bloqueada (Força Bruta).
     */
    public function isLockedOut(string $email, int $maxAttempts): bool {
        return $this->getMapper()->getLoginAttempts($email) >= $maxAttempts;
    }

    /**
     * Registra uma tentativa de login falha.
     */
    public function addLoginAttempt(string $email, string $ip = ''): void {
        $this->getMapper()->addLoginAttempt($email, $ip);
    }

    /**
     * Reseta (limpa) as tentativas de login após uma autenticação bem-sucedida.
     */
    public function resetLoginAttempts(string $email): void {
        $this->getMapper()->deleteLoginAttempts($email);
    }

    /**
     * Valida e registra um novo cliente encapsulando regras de negócio e infraestrutura.
     * 
     * @param array $data Dados vindos do formulário (POST)
     * @return array Array contendo status da transação, erros e ID do cliente.
     */
    public function registerCustomer(array $data): array {
        $errors = [];
        $language = $this->registry->get('language');
        $config = $this->registry->get('config');
        $request = $this->registry->get('request');
        
        $language->load('account/register');

        // Validação de Grupo de Clientes
        $customer_group_id = !empty($data['customer_group_id']) ? (int)$data['customer_group_id'] : (int)$config->get('config_customer_group_id');

        $repoFactory = $this->registry->get('alpha_repository_factory');
        $customerGroupRepo = $repoFactory->get(\Alpha\Model\Domain\Repositories\CustomerGroupRepository::class);
        $customer_group_info = $customerGroupRepo->getCustomerGroup($customer_group_id, (int)$config->get('config_language_id'));

        if (!$customer_group_info || !in_array($customer_group_id, (array)$config->get('config_customer_group_display'))) {
            $errors['warning'] = $language->get('error_customer_group');
        }

        if (!oc_validate_length($data['firstname'] ?? '', 1, 32)) {
            $errors['firstname'] = $language->get('error_firstname');
        }
        if (!oc_validate_length($data['lastname'] ?? '', 1, 32)) {
            $errors['lastname'] = $language->get('error_lastname');
        }
        if (!oc_validate_email($data['email'] ?? '')) {
            $errors['email'] = $language->get('error_email');
        } elseif ($this->findByEmail($data['email'])) {
            $errors['warning'] = $language->get('error_exists');
        }
        if ($config->get('config_telephone_required') && !oc_validate_length($data['telephone'] ?? '', 3, 32)) {
            $errors['telephone'] = $language->get('error_telephone');
        }

        // Validação Algorítmica de Documentos (CPF/CNPJ)
        $personType = $data['persontype'] ?? '';
        $cpfCnpj = $data['cpf_cnpj'] ?? '';

        if ($personType === 'F' && !empty($cpfCnpj)) {
            $cpf = preg_replace('/[^0-9]/', '', (string)$cpfCnpj);
            $cpf_valid = true;

            if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
                $cpf_valid = false;
            } else {
                for ($t = 9; $t < 11; $t++) {
                    for ($d = 0, $c = 0; $c < $t; $c++) {
                        $d += (int)$cpf[$c] * (($t + 1) - $c);
                    }
                    $d = ((10 * $d) % 11) % 10;
                    if ((int)$cpf[$c] !== $d) {
                        $cpf_valid = false;
                        break;
                    }
                }
            }
            if (!$cpf_valid) {
                $errors['cpf_cnpj'] = $language->get('error_cpf') ?: 'O CPF informado é inválido.';
            }
        } elseif ($personType === 'J' && !empty($cpfCnpj)) {
            $cnpj = preg_replace('/[^0-9]/', '', (string)$cpfCnpj);
            $cnpj_valid = true;

            if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
                $cnpj_valid = false;
            } else {
                $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
                for ($i = 0, $n = 0; $i < 12; $n += (int)$cnpj[$i] * $b[++$i]);
                if ((int)$cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) { $cnpj_valid = false; } else {
                    for ($i = 0, $n = 0; $i <= 12; $n += (int)$cnpj[$i] * $b[$i++]);
                    if ((int)$cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) { $cnpj_valid = false; }
                }
            }
            if (!$cnpj_valid) {
                $errors['cpf_cnpj'] = $language->get('error_cnpj') ?: 'O CNPJ informado é inválido.';
            }
        }

        // Validação de Campos Customizados
        $customFieldRepo = $repoFactory->get(\Alpha\Model\Domain\Repositories\CustomFieldRepository::class);
        $custom_fields = $customFieldRepo->getCustomFields($customer_group_id);

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'account') {
                $cf_id = $custom_field['custom_field_id'];
                $is_empty = true;

                if (isset($data['custom_field'][$cf_id])) {
                    $cf_value = $data['custom_field'][$cf_id];
                    if (is_array($cf_value)) {
                        $is_empty = empty($cf_value);
                    } else {
                        $is_empty = (trim((string)$cf_value) === '');
                    }
                }

                if ($custom_field['required'] && $is_empty) {
                    $errors['custom_field_' . $cf_id] = sprintf($language->get('error_custom_field'), $custom_field['name']);
                } elseif ($custom_field['type'] == 'text' && !empty($custom_field['validation']) && !$is_empty) {
                    if (!oc_validate_regex((string)($data['custom_field'][$cf_id] ?? ''), $custom_field['validation'])) {
                        $errors['custom_field_' . $cf_id] = sprintf($language->get('error_regex'), $custom_field['name']);
                    }
                }
            }
        }

        // Validação de Força de Senha
        $password = html_entity_decode($data['password'] ?? '', ENT_QUOTES, 'UTF-8');

        if (!oc_validate_length($password, (int)$config->get('config_password_length'), 40)) {
            $errors['password'] = sprintf($language->get('error_password_length'), (int)$config->get('config_password_length'));
        }

        $required = [];
        if ($config->get('config_password_uppercase') && !preg_match('/[A-Z]/', $password)) { $required[] = $language->get('error_password_uppercase'); }
        if ($config->get('config_password_lowercase') && !preg_match('/[a-z]/', $password)) { $required[] = $language->get('error_password_lowercase'); }
        if ($config->get('config_password_number') && !preg_match('/[0-9]/', $password)) { $required[] = $language->get('error_password_number'); }
        if ($config->get('config_password_symbol') && !preg_match('/[^a-zA-Z0-9]/', $password)) { $required[] = $language->get('error_password_symbol'); }

        if ($required) {
            $errors['password'] = sprintf($language->get('error_password'), implode(', ', $required), $config->get('config_password_length'));
        }

        // Aceite dos Termos de Uso
        $informationRepo = $repoFactory->get(\Alpha\Model\Domain\Repositories\InformationRepository::class);
        $information_info = $informationRepo->getInformation((int)$config->get('config_account_id'));
        if ($information_info && empty($data['agree'])) {
            $errors['warning'] = sprintf($language->get('error_agree'), $information_info['title']);
        }

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
        $data['ip'] = $request->server['REMOTE_ADDR'] ?? '127.0.0.1';
        $data['token'] = '';
        $data['code'] = '';
        $data['date_added'] = date('Y-m-d H:i:s');

        $customer = new Customer();
        
        EntityMapper::fillEntity($customer, $data);

        $customer->setStoreId((int)$config->get('config_store_id'))
                 ->setLanguageId((int)$config->get('config_language_id'))
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
     * Atualiza o perfil completo do cliente através da Entidade.
     */
    public function updateProfile(Customer $customer): void {
        $this->getMapper()->update($customer);
    }

    /**
     * Validações de domínio para edição de conta do cliente.
     * @return array Array contendo os erros encontrados. Vazio se válido.
     */
    public function validateEditData(array $data, int $customerId): array {
        $errors = [];
        $language = $this->registry->get('language');
        $config = $this->registry->get('config');
        
        $language->load('account/edit');

        if (!oc_validate_length($data['firstname'], 1, 32)) {
            $errors['firstname'] = $language->get('error_firstname');
        }
        if (!oc_validate_length($data['lastname'], 1, 32)) {
            $errors['lastname'] = $language->get('error_lastname');
        }
        if (!oc_validate_email($data['email'])) {
            $errors['email'] = $language->get('error_email');
        }

        $existingCustomer = $this->findByEmail($data['email']);
        if ($existingCustomer && $existingCustomer->getId() !== $customerId) {
            $errors['warning'] = $language->get('error_exists');
        }

        if ($config->get('config_telephone_required') && !oc_validate_length($data['telephone'], 3, 32)) {
            $errors['telephone'] = $language->get('error_telephone');
        }

        // Validação Algorítmica de Documentos (CPF/CNPJ)
        $personType = $data['persontype'] ?? '';
        $cpfCnpj = $data['cpf_cnpj'] ?? '';

        if ($personType === 'F' && !empty($cpfCnpj)) {
            $cpf = preg_replace('/[^0-9]/', '', (string)$cpfCnpj);
            $cpf_valid = true;

            if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
                $cpf_valid = false;
            } else {
                for ($t = 9; $t < 11; $t++) {
                    for ($d = 0, $c = 0; $c < $t; $c++) {
                        $d += (int)$cpf[$c] * (($t + 1) - $c);
                    }
                    $d = ((10 * $d) % 11) % 10;
                    if ((int)$cpf[$c] !== $d) {
                        $cpf_valid = false;
                        break;
                    }
                }
            }
            if (!$cpf_valid) {
                $errors['cpf_cnpj'] = $language->get('error_cpf') ?: 'O CPF informado é inválido.';
            }
        } elseif ($personType === 'J' && !empty($cpfCnpj)) {
            $cnpj = preg_replace('/[^0-9]/', '', (string)$cpfCnpj);
            $cnpj_valid = true;

            if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
                $cnpj_valid = false;
            } else {
                $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
                for ($i = 0, $n = 0; $i < 12; $n += (int)$cnpj[$i] * $b[++$i]);
                if ((int)$cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) { $cnpj_valid = false; } else {
                    for ($i = 0, $n = 0; $i <= 12; $n += (int)$cnpj[$i] * $b[$i++]);
                    if ((int)$cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) { $cnpj_valid = false; }
                }
            }
            if (!$cnpj_valid) {
                $errors['cpf_cnpj'] = $language->get('error_cnpj') ?: 'O CNPJ informado é inválido.';
            }
        }

        // Validação Estrita (Type-Safe) de Campos Customizados
        $customer = $this->find($customerId);
        $customerGroupId = $customer ? $customer->getGroupId() : (int)$config->get('config_customer_group_id');
        $customFieldRepo = $this->registry->get('alpha_repository_factory')->get(\Alpha\Model\Domain\Repositories\CustomFieldRepository::class);
        $custom_fields = $customFieldRepo->getCustomFields($customerGroupId);

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'account') {
                $cf_id = $custom_field['custom_field_id'];
                $is_empty = true;

                if (isset($data['custom_field'][$cf_id])) {
                    $cf_value = $data['custom_field'][$cf_id];
                    if (is_array($cf_value)) {
                        $is_empty = empty($cf_value);
                    } else {
                        $is_empty = (trim((string)$cf_value) === '');
                    }
                }

                if ($custom_field['required'] && $is_empty) {
                    $errors['custom_field_' . $cf_id] = sprintf($language->get('error_custom_field'), $custom_field['name']);
                } elseif ($custom_field['type'] == 'text' && !empty($custom_field['validation']) && !$is_empty) {
                    if (!oc_validate_regex((string)($data['custom_field'][$cf_id] ?? ''), $custom_field['validation'])) {
                        $errors['custom_field_' . $cf_id] = sprintf($language->get('error_regex'), $custom_field['name']);
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validações de domínio para alteração de senha.
     * @return array Array contendo os erros encontrados. Vazio se válido.
     */
    public function validatePasswordData(array $data): array {
        $errors = [];
        $language = $this->registry->get('language');
        $config = $this->registry->get('config');
        
        $language->load('account/password');
        $password = html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8');

        if (!oc_validate_length($password, (int)$config->get('config_password_length'), 40)) {
            $errors['password'] = sprintf($language->get('error_password_length'), $config->get('config_password_length'));
        }

        $required = [];
        if ($config->get('config_password_uppercase') && !preg_match('/[A-Z]/', $password)) { $required[] = $language->get('error_password_uppercase'); }
        if ($config->get('config_password_lowercase') && !preg_match('/[a-z]/', $password)) { $required[] = $language->get('error_password_lowercase'); }
        if ($config->get('config_password_number') && !preg_match('/[0-9]/', $password)) { $required[] = $language->get('error_password_number'); }
        if ($config->get('config_password_symbol') && !preg_match('/[^a-zA-Z0-9]/', $password)) { $required[] = $language->get('error_password_symbol'); }

        if ($required) {
            $errors['password'] = sprintf($language->get('error_password'), implode(', ', $required), $config->get('config_password_length'));
        }
        if ($data['confirm'] != $data['password']) {
            $errors['confirm'] = $language->get('error_confirm');
        }

        return $errors;
    }

    /**
     * Persiste (Salva) um novo cliente na base de dados.
     * 
     * @param Customer $customer
     * @return int O ID do cliente inserido.
     */
    public function save(Customer $customer): ?int {
        $id = $this->getMapper()->save($customer);
        if (!$id) {
            throw new \RuntimeException("Alpha Engine: Falha de Persistência. O DAO retornou nulo ao tentar salvar o Customer. Verifique o arquivo storage/logs/error.log para identificar qual coluna o banco de dados rejeitou (ex: restrição NOT NULL em colunas como 'ip' ou 'token').");
        }
        return $id;
    }

    // BaseRepositoryInterface bindings
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { 
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset); 
    }
    public function findOneBy(array $criteria): ?InterfaceEntity { 
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
    public function findAll(): array {
        return $this->getMapper()->findAll();
    }
}