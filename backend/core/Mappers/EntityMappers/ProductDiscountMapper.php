<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ProductDiscount;

/**
 * ProductDiscountMapper - Gerencia a persistência dos descontos por quantidade do produto.
 */
class ProductDiscountMapper extends BaseMapper
{
    protected string $table = 'product_discount';
    protected string $entityClass = ProductDiscount::class;
}