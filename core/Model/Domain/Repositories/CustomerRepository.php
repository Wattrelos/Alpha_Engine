<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CustomerMapper;
use Alpha\Model\Domain\Entities\Customer;
use Alpha\Model\Domain\InterfaceEntity;

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
     * Validações rigorosas de domínio para registro/edição de cliente.
     * @return array Array contendo os erros encontrados. Vazio se válido.
     */
    public function validateRegistrationData(array $data): array {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'O e-mail fornecido é inválido!';
        } elseif ($this->findByEmail($data['email'])) {
            $errors['email'] = 'Atenção: Este e-mail já está registrado!';
        }

        return $errors;
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