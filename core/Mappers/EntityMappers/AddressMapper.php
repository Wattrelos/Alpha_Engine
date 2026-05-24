<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Address;

/**
 * Mapper para a entidade Address.
 * Isola a camada de banco de dados (tabela address).
 */
class AddressMapper extends BaseMapper
{
    protected string $table = 'address';
    protected string $entityClass = Address::class;
}