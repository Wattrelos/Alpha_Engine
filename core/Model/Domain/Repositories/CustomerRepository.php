<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\CustomerMapper;
use Alpha\Model\Domain\Entities\Customer;

/**
 * Class CustomerRepository
 * 
 * Camada de domínio para a gestão de clientes.
 * Centraliza a lógica de autenticação (password_verify), controle de 
 * força bruta (login attempts) e abstrai as operações do CustomerMapper.
 */
class CustomerRepository extends AbstractRepository {

    public function __construct(CustomerMapper $mapper) {
        parent::__construct($mapper);
    }

    /**
     * Busca a entidade de um cliente através do seu e-mail.
     */
    public function findByEmail(string $email): ?Customer {
        /** @var CustomerMapper $mapper */
        $mapper = $this->getMapper();
        return $mapper->findByEmail($email);
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
        /** @var CustomerMapper $mapper */
        $mapper = $this->getMapper();
        $hash = password_hash($rawPassword, PASSWORD_DEFAULT);
        $mapper->updatePassword($customerId, $hash);
    }

    /**
     * Verifica se a conta do cliente está temporariamente bloqueada (Força Bruta).
     */
    public function isLockedOut(string $email, int $maxAttempts): bool {
        /** @var CustomerMapper $mapper */
        $mapper = $this->getMapper();
        $attempts = $mapper->getLoginAttempts($email);
        
        return $attempts >= $maxAttempts;
    }

    /**
     * Registra uma tentativa de login falha.
     */
    public function addLoginAttempt(string $email): void {
        /** @var CustomerMapper $mapper */
        $mapper = $this->getMapper();
        $mapper->addLoginAttempt($email);
    }

    /**
     * Reseta (limpa) as tentativas de login após uma autenticação bem-sucedida.
     */
    public function resetLoginAttempts(string $email): void {
        /** @var CustomerMapper $mapper */
        $mapper = $this->getMapper();
        $mapper->deleteLoginAttempts($email);
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
}