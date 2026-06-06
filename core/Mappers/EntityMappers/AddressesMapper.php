<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Supplier\Addresses;

/**
 * Mapper para gerenciar a lógica de Endereços de Fornecedores
 */
class AddressesMapper extends BaseMapper
{

    protected string $tableName = 'supplier_addresses';
    protected string $entityClass = Addresses::class;
}
