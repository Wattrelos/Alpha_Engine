<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\GeoZoneMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * GeoZoneRepository
 * 
 * Abstrai a lógica de localização geográfica e verificação de zonas,
 * muito utilizada em validações de frete e regras de impostos.
 */
class GeoZoneRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): GeoZoneMapper
    {
        return $this->mapperFactory->get(GeoZoneMapper::class);
    }

    public function getGeoZones(): array
    {
        $cacheKey = 'geo_zones.all';
        
        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $zones = $this->getMapper()->findAll();

        if ($this->cache !== null) {
            // Zonas geográficas mudam raramente. Cache longo de 24h.
            $this->cache->set($cacheKey, $zones, 86400); 
        }

        return $zones;
    }

    public function isAddressInGeoZone(int $geoZoneId, array $address): bool
    {
        $countryId = (int)($address['country_id'] ?? 0);
        $zoneId = (int)($address['zone_id'] ?? 0);

        return $this->getMapper()->checkAddressInZone($geoZoneId, $countryId, $zoneId);
    }

    // Implementações obrigatórias da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return $this->getGeoZones(); }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}