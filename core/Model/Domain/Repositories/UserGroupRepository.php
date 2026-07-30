<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\UserGroupMapper;
use Alpha\Model\Domain\Entities\UserGroup;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * UserGroupRepository
 * Gerencia os perfis e permissões do painel de controle.
 */
class UserGroupRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = UserGroupMapper::class;

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

    public function save(UserGroup $userGroup): ?int
    {
        return $this->getMapper()->save($userGroup);
    }

    public function delete(int $id): bool
    {
        return $this->getMapper()->delete($id);
    }

    public function countUsersInGroup(int $userGroupId): int
    {
        return $this->getMapper()->countUsersInGroup($userGroupId);
    }
}
