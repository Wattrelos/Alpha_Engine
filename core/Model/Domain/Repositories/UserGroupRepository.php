<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\UserGroupMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * UserGroupRepository
 * Gerencia os perfis e permissões do painel de controle.
 */
class UserGroupRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): UserGroupMapper
    {
        return $this->mapperFactory->get(UserGroupMapper::class);
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
        return $this->getMapper()->findOneBy($criteria);
    }
}