<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProductDiscountMapper;
use Alpha\Model\Domain\Entities\ProductDiscount;

/**
 * ProductDiscountRepository
 * 
 * Gerencia os descontos progressivos de produtos (ex: "Compre 5 e ganhe 10%").
 */
class ProductDiscountRepository
{
    private ProductDiscountMapper $mapper;

    public function __construct()
    {
        $this->mapper = new ProductDiscountMapper();
    }

    /**
     * Retorna todos os descontos vinculados a um produto.
     */
    public function getByProductId(int $productId): array
    {
        return $this->mapper->search(['productId' => $productId]);
    }

    /**
     * Retorna os descontos de um produto aplicáveis a um grupo de clientes específico.
     * Extremamente útil para recalcular o total do carrinho dinamicamente.
     */
    public function getActiveDiscounts(int $productId, int $customerGroupId): array
    {
        return $this->mapper->search([
            'productId' => $productId,
            'customerGroupId' => $customerGroupId
        ]);
    }
}