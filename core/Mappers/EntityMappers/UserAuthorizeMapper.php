<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\UserAuthorize;

/**
 * Mapper para a entidade UserAuthorize.
 * Isola o banco de dados (tabela user_authorize) da lógica de domínio.
 */
class UserAuthorizeMapper extends BaseMapper
{
    protected string $table = 'user_authorize';
    protected string $entityClass = UserAuthorize::class;
}