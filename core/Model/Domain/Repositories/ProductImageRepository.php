<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProductImageMapper;
use Alpha\Model\Domain\Entities\ProductImage;

/**
 * ProductImageRepository
 * 
 * Gerencia a galeria de imagens secundárias dos produtos.
 */
class ProductImageRepository
{
    private ProductImageMapper $mapper;

    public function __construct()
    {
        $this->mapper = new ProductImageMapper();
    }

    /**
     * Retorna a lista de imagens extras de um produto,
     * tipicamente ordenadas pela propriedade sortOrder (se mapeada).
     */
    public function getByProductId(int $productId): array
    {
        return $this->mapper->search(['productId' => $productId]);
    }
}