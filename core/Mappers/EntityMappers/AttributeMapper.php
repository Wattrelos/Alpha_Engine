<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Attribute;

/**
 * Mapper para a entidade Attribute.
 * Isola a camada de banco de dados (tabela attribute).
 */
class AttributeMapper extends BaseMapper
{
    protected string $table = 'attribute';
    protected string $entityClass = Attribute::class;
}