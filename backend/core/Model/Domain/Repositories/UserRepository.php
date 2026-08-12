<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\UserMapper;
use Alpha\Model\Domain\Entities\User;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * UserRepository
 * Ponto central para acesso aos dados e validações dos administradores da loja.
 */
class UserRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): UserMapper
    {
        return $this->mapperFactory->get(UserMapper::class);
    }

    /**
     * Busca um administrador pelo nome de usuário.
     * 
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User
    {
        $results = $this->getMapper()->search(['username' => $username]);
        return $results[0] ?? null;
    }

    /**
     * Busca um administrador pelo endereço de email.
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        $results = $this->getMapper()->search(['email' => $email]);
        return $results[0] ?? null;
    }

    public function find(int $id): ?InterfaceEntity
    {
        return $this->getMapper()->findById($id);
    }

    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }

    public function getIndexData(array $filters = []): array
    {
        return $this->getMapper()->search($filters);
    }

    /**
     * Obtém a quantidade de tentativas falhas de login.
     */
    public function getLoginAttempts(string $username): int
    {
        return $this->getMapper()->getLoginAttempts($username);
    }

    /**
     * Verifica se a conta do administrador está bloqueada temporariamente.
     */
    public function isLockedOut(string $username, int $maxAttempts): bool
    {
        return $this->getLoginAttempts($username) >= $maxAttempts;
    }

    /**
     * Registra uma tentativa de login falha.
     */
    public function addLoginAttempt(string $username, string $ip = ''): void
    {
        $this->getMapper()->addLoginAttempt($username, $ip);
    }

    /**
     * Reseta as tentativas de login após autenticação bem-sucedida.
     */
    public function resetLoginAttempts(string $username): void
    {
        $this->getMapper()->deleteLoginAttempts($username);
    }

    public function save(User $user): ?int
    {
        return $this->getMapper()->save($user);
    }

    public function delete(int $id): bool
    {
        return $this->getMapper()->delete($id);
    }

    public function getPaginatedUsers(array $filters = [], int $page = 1, int $limit = 10): array
    {
        return $this->getMapper()->paginate($filters, $page, $limit);
    }
}
