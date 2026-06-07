<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\UserAuthorizeMapper;
use Alpha\Model\Domain\Entities\UserAuthorize;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * UserAuthorizeRepository
 * Gerencia as autorizações de dispositivos do painel administrativo.
 */
class UserAuthorizeRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = UserAuthorizeMapper::class;

    protected function getMapper(): UserAuthorizeMapper
    {
        return $this->mapperFactory->get(UserAuthorizeMapper::class);
    }

    /**
     * Busca uma autorização de sessão administrativa com base no token.
     * 
     * @param string $token
     * @return UserAuthorize|null
     */
    public function findByToken(string $token): ?UserAuthorize
    {
        $results = $this->getMapper()->search(['token' => $token]);
        return $results[0] ?? null;
    }

    /**
     * Encerra (exclui) uma sessão autorizada de administrador.
     */
    public function revokeAuthorization(int $id): bool
    {
        return $this->getMapper()->delete($id);
    }

    // BaseRepositoryInterface bindings
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
}
