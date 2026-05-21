<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\StoreMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * Class StoreRepository
 * 
 * Autoridade de domínio para Lojas e resoluções de Multi-Store.
 */
class StoreRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): StoreMapper
    {
        return $this->mapperFactory->get(StoreMapper::class);
    }

    public function getStoreByHostname(string $hostname): ?array
    {
        return $this->getMapper()->getStoreByHostname($hostname);
    }

    // Implementações obrigatórias da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}