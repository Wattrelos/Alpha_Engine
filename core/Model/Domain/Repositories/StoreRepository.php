<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\StoreMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * StoreRepository - Autoridade de Domínio para Lojas (Store).
 */
class StoreRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): StoreMapper
    {
        return $this->mapperFactory->get(StoreMapper::class);
    }

    public function getStore(int $store_id): array
    {
        return $this->getMapper()->getStore($store_id);
    }

    public function getStoreByHostname(string $hostname): array
    {
        return $this->getMapper()->getStoreByHostname($hostname) ?? [];
    }

    public function getStores(): array
    {
        return $this->getMapper()->getStores();
    }



    // Métodos obrigatórios da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity
    {
        return null;
    }
    public function findAll(): array
    {
        return [];
    }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return null;
    }
}
