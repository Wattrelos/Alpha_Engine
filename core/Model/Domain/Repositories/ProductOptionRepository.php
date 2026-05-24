<?php

namespace Alpha\Mappers\EntityMappers; // namespace fallback if required, mas será usado Alpha\Model\Domain\Repositories

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProductOptionMapper;
use Alpha\Model\Domain\Entities\ProductOption;

/**
 * ProductOptionRepository
 * 
 * Gerencia a recuperação e orquestração das opções de produtos, 
 * provendo métodos isolados para regras de negócio do Catálogo.
 */
class ProductOptionRepository
{
    private ProductOptionMapper $mapper;

    public function __construct()
    {
        $this->mapper = new ProductOptionMapper();
    }

    public function findById(int $id): ?ProductOption
    {
        return $this->mapper->findById($id);
    }

    /**
     * Retorna todas as opções vinculadas a um produto.
     */
    public function getByProductId(int $productId): array
    {
        return $this->mapper->search(['productId' => $productId]);
    }
}