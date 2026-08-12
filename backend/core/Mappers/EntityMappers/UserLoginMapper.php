<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\UserLogin;

/**
 * Mapper para a entidade UserLogin.
 * Isola a camada de persistência da tabela user_login.
 */
class UserLoginMapper extends BaseMapper
{
    protected string $table = 'user_login';
    protected string $entityClass = UserLogin::class;
}