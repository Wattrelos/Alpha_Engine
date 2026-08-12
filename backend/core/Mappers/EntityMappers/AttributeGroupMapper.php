<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\AttributeGroup;

/**
 * Mapper para a entidade AttributeGroup.
 * Isola a camada de banco de dados (tabela attribute_group).
 */
class AttributeGroupMapper extends BaseMapper
{
    protected string $tableName = 'attribute_group';
    protected string $entityClass = AttributeGroup::class;
}