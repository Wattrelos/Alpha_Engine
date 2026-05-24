<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\User;

/**
 * Mapper para a entidade User.
 * Isola a camada de persistência da tabela user.
 */
class UserMapper extends BaseMapper
{
    protected string $table = 'user';
    protected string $entityClass = User::class;
}