<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\CustomerToken;

/**
 * Mapper para a entidade CustomerToken.
 * Isola o banco de dados (tabela customer_token) da lógica de domínio.
 */
class CustomerTokenMapper extends BaseMapper
{
    protected string $table = 'customer_token';
    protected string $entityClass = CustomerToken::class;
}