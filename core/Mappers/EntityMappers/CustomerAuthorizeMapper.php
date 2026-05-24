<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\CustomerAuthorize;

/**
 * Mapper para a entidade CustomerAuthorize.
 * Isola o banco de dados (tabela customer_authorize) da lógica de domínio.
 */
class CustomerAuthorizeMapper extends BaseMapper
{
    protected string $table = 'customer_authorize';
    protected string $entityClass = CustomerAuthorize::class;
}