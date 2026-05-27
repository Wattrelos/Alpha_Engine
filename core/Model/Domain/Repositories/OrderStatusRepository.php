<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\OrderStatusMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * OrderStatusRepository - Autoridade de Domínio para Status de Pedidos.
 */
class OrderStatusRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): OrderStatusMapper
    {
        return $this->mapperFactory->get(OrderStatusMapper::class);
    }

    public function getOrderStatus(int $orderStatusId): array
    {
        return $this->getMapper()->getOrderStatus($orderStatusId, $this->language_id);
    }

    public function getOrderStatuses(): array
    {
        $cacheKey = 'order_status.lang.' . $this->language_id;

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = $this->getMapper()->getOrderStatuses($this->language_id);

        if ($this->cache) {
            $this->cache->set($cacheKey, $results);
        }

        return $results;
    }

    // BaseRepositoryInterface bindings
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}