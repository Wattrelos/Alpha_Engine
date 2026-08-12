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
        $langId = $this->getLanguageId();
        return $this->getMapper()->findWithLanguage($id, $langId);
    }

    public function findWithLanguage(int $id, int $languageId): ?UserGroup
    {
        return $this->getMapper()->findWithLanguage($id, $languageId);
    }

    public function findAll(): array
    {
        $langId = $this->getLanguageId();
        return $this->getMapper()->findAllWithLanguage($langId);
    }

    public function findAllWithLanguage(int $languageId): array
    {
        return $this->getMapper()->findAllWithLanguage($languageId);
    }

    public function findDescriptions(int $userGroupId): array
    {
        return $this->getMapper()->findDescriptions($userGroupId);
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
        $id = $this->getMapper()->save($userGroup);
        if ($id && !empty($userGroup->getDescriptions())) {
            $this->getMapper()->saveDescriptions($id, $userGroup->getDescriptions());
        }
        return $id;
    }

    public function saveWithDescriptions(UserGroup $userGroup, array $namesByLanguage): ?int
    {
        $id = $this->getMapper()->save($userGroup);
        if ($id && !empty($namesByLanguage)) {
            $this->getMapper()->saveDescriptions($id, $namesByLanguage);
        }
        return $id;
    }

    public function delete(int $id): bool
    {
        $this->getMapper()->deleteDescriptions($id);
        return $this->getMapper()->delete($id);
    }

    public function countUsersInGroup(int $userGroupId): int
    {
        return $this->getMapper()->countUsersInGroup($userGroupId);
    }
}
