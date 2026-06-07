<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\OrderStatusMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * OrderStatusRepository - Autoridade de Domínio para Status de Pedidos.
 */
class OrderStatusRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = OrderStatusMapper::class;

    protected function getMapper(): OrderStatusMapper
    {
        return $this->mapperFactory->get(OrderStatusMapper::class);
    }

    public function getOrderStatus(int $orderStatusId): array
    {
        $status = $this->getMapper()->getOrderStatus($orderStatusId, $this->language_id);
        return $status ? $status->toArray() : [];
    }

    public function getOrderStatuses(): array
    {
        $cacheKey = 'order_status.lang.' . $this->language_id;

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = [];
        $statuses = $this->getMapper()->getOrderStatuses($this->language_id);
        foreach ($statuses as $status) {
            $results[] = $status->toArray();
        }

        if ($this->cache) {
            $this->cache->set($cacheKey, $results);
        }

        return $results;
    }

    // BaseRepositoryInterface bindings
    public function find(int $id): ?InterfaceEntity
    {
        return $this->getMapper()->getOrderStatus($id, $this->language_id);
    }

    public function findAll(): array
    {
        return $this->getMapper()->getOrderStatuses($this->language_id);
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $criteria['language_id'] = $criteria['language_id'] ?? $this->language_id;
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $criteria['language_id'] = $criteria['language_id'] ?? $this->language_id;
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
}
