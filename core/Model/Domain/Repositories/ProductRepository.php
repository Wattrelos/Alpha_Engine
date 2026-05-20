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