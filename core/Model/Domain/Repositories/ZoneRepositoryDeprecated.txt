<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ZoneMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * ZoneRepository - Autoridade de Domínio para Zonas (Estados/Províncias).
 */
class ZoneRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): ZoneMapper
    {
        return $this->mapperFactory->get(ZoneMapper::class);
    }

    public function getZone(int $zoneId): array
    {
        return $this->getMapper()->getZone($zoneId, $this->language_id);
    }

    public function getZonesByCountryId(int $countryId): array
    {
        $cacheKey = 'zone.country.' . $countryId . '.lang.' . $this->language_id;

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = $this->getMapper()->getZonesByCountryId($countryId, $this->language_id);

        if ($this->cache) {
            $this->cache->set($cacheKey, $results);
        }

        return $results;
    }

    public function getTotalZonesByCountryId(int $countryId): int
    {
        return $this->getMapper()->getTotalZonesByCountryId($countryId);
    }

    // BaseRepositoryInterface bindings
    public function find(int $id): ?InterfaceEntity
    {
        return $this->getMapper()->findById($id);
    }

    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
}
