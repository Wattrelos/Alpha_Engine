<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\UserGroup;

/**
 * Mapper para a entidade UserGroup.
 * Isola a camada de persistência da tabela user_group.
 */
class UserGroupMapper extends BaseMapper
{
    protected string $tableName = 'user_group';
    protected string $table = 'user_group';
    protected string $entityClass = UserGroup::class;

    /**
     * Conta quantos usuários/funcionários estão vinculados a um grupo.
     */
    public function countUsersInGroup(int $userGroupId): int
    {
        $query = (new \Alpha\Model\DataAccessObject\QueryBuilder())
            ->from(DB_PREFIX . 'user')
            ->where("user_group_id = ?", [$userGroupId])
            ->select('COUNT(*) AS total');

        $results = $this->dao->executeQuery($query);
        return $results ? (int)$results[0]['total'] : 0;
    }
}