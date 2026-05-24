<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ProductOption;

/**
 * ProductOptionMapper - Gerencia a persistência das opções atreladas aos produtos.
 */
class ProductOptionMapper extends BaseMapper
{
    protected string $table = 'product_option';
    protected string $entityClass = ProductOption::class;
}