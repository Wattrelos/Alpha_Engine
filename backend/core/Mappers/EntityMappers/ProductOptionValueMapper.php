<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ProductOptionValue;

/**
 * ProductOptionValueMapper - Gerencia a persistência dos valores das opções atreladas aos produtos.
 */
class ProductOptionValueMapper extends BaseMapper
{
    protected string $table = 'product_option_value';
    protected string $entityClass = ProductOptionValue::class;
}