<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProductOptionValueMapper;
use Alpha\Model\Domain\Entities\ProductOptionValue;

/**
 * ProductOptionValueRepository
 * 
 * Orquestra os valores de opções (tamanhos, cores, pesos adicionais),
 * com métodos otimizados O(1) para cálculos do carrinho de compras.
 */
class ProductOptionValueRepository
{
    private ProductOptionValueMapper $mapper;

    public function __construct()
    {
        $this->mapper = new ProductOptionValueMapper();
    }

    public function findById(int $id): ?ProductOptionValue
    {
        return $this->mapper->findById($id);
    }

    /**
     * Retorna todos os valores atrelados a uma opção específica de um produto.
     */
    public function getByProductOptionId(int $productOptionId): array
    {
        return $this->mapper->search(['productOptionId' => $productOptionId]);
    }

    /**
     * Retorna valores específicos baseados em múltiplos IDs, em lote.
     * Utilizado intensivamente pela página do carrinho para recuperar modificadores.
     * Os dados são retornados indexados pelo ID para matemática O(1).
     * 
     * @param array $ids Array de product_option_value_id escolhidos pelo cliente
     * @return array<int, ProductOptionValue>
     */
    public function getOptionValuesByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $indexedValues = [];
        foreach ($ids as $id) {
            $optionValue = $this->findById((int)$id);
            if ($optionValue) {
                $indexedValues[$optionValue->getId()] = $optionValue;
            }
        }

        return $indexedValues;
    }
}