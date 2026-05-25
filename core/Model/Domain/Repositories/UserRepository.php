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
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset);
    }
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
}