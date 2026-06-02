<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\SubscriptionStatusMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * SubscriptionStatusRepository - Autoridade de Domínio para Status de Assinaturas.
 */
class SubscriptionStatusRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): SubscriptionStatusMapper
    {
        return $this->mapperFactory->get(SubscriptionStatusMapper::class);
    }

    public function getSubscriptionStatus(int $subscriptionStatusId): array
    {
        return $this->getMapper()->getSubscriptionStatus($subscriptionStatusId, $this->language_id);
    }

    public function getSubscriptionStatuses(): array
    {
        $cacheKey = 'subscription_status.lang.' . $this->language_id;

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = $this->getMapper()->getSubscriptionStatuses($this->language_id);

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
