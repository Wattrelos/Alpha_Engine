<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\UserToken;

/**
 * Mapper para a entidade UserToken.
 * Isola o banco de dados (tabela user_token) da lógica de domínio.
 */
class UserTokenMapper extends BaseMapper
{
    protected string $table = 'user_token';
    protected string $entityClass = UserToken::class;
}