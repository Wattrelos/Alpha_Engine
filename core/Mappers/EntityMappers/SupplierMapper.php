<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Supplier\Supplier;

/**
 * Mapper para gerenciar a lógica de Fornecedores (Suppliers)
 */
class SupplierMapper extends BaseMapper {
    
    protected string $tableName = 'suppliers';
    protected string $entityClass = Supplier::class;
}
