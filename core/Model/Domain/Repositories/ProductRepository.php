<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * ProductRepository - Autoridade de Domínio para Produtos.
 * 
 * Centraliza a recuperação de produtos, delegando a lógica de persistência 
 * ao ProductMapper e garantindo que o domínio trabalhe com entidades tipadas.
 */
class ProductRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Alpha Engine: Recupera o Grafo Completo do Produto (Detalhes)
     * 
     * @param int $productId
     * @return array|null
     */
    public function getProduct(int $productId): ?array
    {
        $customerGroupId = $this->customer->isLogged() 
            ? (int)$this->customer->getGroupId() 
            : (int)$this->config->get('config_customer_group_id');

        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        return $mapper->getProduct($productId, $this->language_id, $this->store_id, $customerGroupId);
    }

    /**
     * Alpha Engine: Registra visualização de produto.
     */
    public function addReport(int $productId, string $ip): void
    {
        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        if (method_exists($mapper, 'addReport')) {
            $mapper->addReport($productId, $this->store_id, $ip);
        }
    }

    /**
     * Alpha Engine: Recupera produtos com filtros aplicados.
     */
    public function getProducts(array $filterData): array
    {
        $customerGroupId = $this->customer->isLogged() 
            ? (int)$this->customer->getGroupId() 
            : (int)$this->config->get('config_customer_group_id');

        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        return $mapper->getProducts($filterData, $this->language_id, $this->store_id, $customerGroupId);
    }

    /**
     * Alpha Engine: Conta o total de produtos para paginação.
     */
    public function getTotalProducts(array $filterData): int
    {
        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        return $mapper->getTotalProducts($filterData, $this->language_id, $this->store_id);
    }

    /**
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        return $this->mapperFactory->get(ProductMapper::class)->findById($id);
    }

    /**
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        return $this->mapperFactory->get(ProductMapper::class)->findAll();
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get(ProductMapper::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return $this->mapperFactory->get(ProductMapper::class)->findOneBy($criteria);
    }
}