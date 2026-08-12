<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ProductImage;

/**
 * ProductImageMapper - Gerencia a persistência das imagens adicionais da galeria do produto.
 */
class ProductImageMapper extends BaseMapper
{
    protected string $table = 'product_image';
    protected string $entityClass = ProductImage::class;
}