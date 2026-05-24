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
    protected string $table = 'user_group';
    protected string $entityClass = UserGroup::class;
}