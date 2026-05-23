<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ZoneMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Entities\Zone;

/**
 * ZoneRepository - Autoridade de Domínio para Zonas (Estados/Províncias).
 *
 * Aplica o padrão DTO Factory para retrocompatibilidade com o checkout legado,
 * garantindo que as chamadas AJAX do OpenCart não quebrem, enquanto fornece
 * Entidades ricas e Identity Map para o motor da Alpha Engine.
 */
class ZoneRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): ZoneMapper
    {
        return $this->mapperFactory->get(ZoneMapper::class);
    }

    /**
     * [DOMAIN] Busca a Entidade Rica da Zona pelo ID.
     */
    public function find(int $id): ?Zone
    {
        $cacheKey = "zone.entity.{$id}";

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $zone = $this->getMapper()->findById($id);

        if ($zone && $this->cache !== null) {
            $this->cache->set($cacheKey, $zone, 86400);
        }

        return $zone;
    }

    /**
     * [DOMAIN] Lista todas as Entidades Ricas de zonas.
     */
    public function findAll(): array
    {
        $cacheKey = "zone.entity.all";

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $zones = $this->getMapper()->findAll();

        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $zones, 86400);
        }

        return $zones;
    }

    /**
     * [DOMAIN] Lista Zonas por ID de País (Entidades Ricas).
     */
    public function findByCountryId(int $countryId): array
    {
        $cacheKey = "zone.entity.country.{$countryId}";

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        // Busca utilizando a camada de persistência abstrata
        $zones = $this->getMapper()->findBy(['country_id' => $countryId], ['name' => 'ASC']);

        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $zones, 86400);
        }

        return $zones;
    }

    /**
     * [LEGACY DTO] Retorna uma zona formatada como Array Plano para views.
     */
    public function getZone(int $zone_id): array
    {
        $zone = $this->find($zone_id);
        return $zone ? $this->toLegacyDTO($zone) : [];
    }

    /**
     * [LEGACY DTO] Retorna todas as zonas de um país como Arrays Planos para requisições AJAX do Checkout.
     */
    public function getZonesByCountryId(int $country_id): array
    {
        return array_map(fn($z) => $this->toLegacyDTO($z), $this->findByCountryId($country_id));
    }

    /**
     * [LEGACY DTO] Retorna todas as zonas como Arrays Planos.
     */
    public function getZones(): array
    {
        return array_map(fn($z) => $this->toLegacyDTO($z), $this->findAll());
    }

    /**
     * Converte a Entidade Zone num DTO reconhecido pelo padrão OpenCart.
     * Extrai o nome traduzido nativamente e garante compatibilidade de tipos.
     */
    private function toLegacyDTO(Zone $zone): array
    {
        $name = method_exists($zone, 'getName') ? $zone->getName() : '';
        
        if (method_exists($zone, 'getDescriptions') && !empty($zone->getDescriptions())) {
            $langId = property_exists($this, 'registry') && $this->registry 
                        ? (int)$this->registry->get('config')->get('config_language_id') 
                        : null;

            foreach ($zone->getDescriptions() as $desc) {
                if ($langId !== null && method_exists($desc, 'getLanguageId') && $desc->getLanguageId() === $langId) {
                    $name = method_exists($desc, 'getName') ? $desc->getName() : $name;
                    break;
                }
                $name = method_exists($desc, 'getName') ? $desc->getName() : $name;
            }
        }

        return [
            'zone_id'    => $zone->getId(),
            'country_id' => method_exists($zone, 'getCountryId') ? $zone->getCountryId() : 0,
            'name'       => $name,
            'code'       => method_exists($zone, 'getCode') ? $zone->getCode() : '',
            'status'     => method_exists($zone, 'getStatus') ? (int)$zone->getStatus() : 0,
        ];
    }

    // BaseRepositoryInterface bindings remanescentes
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { 
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset); 
    }
    public function findOneBy(array $criteria): ?InterfaceEntity { 
        return $this->getMapper()->findOneBy($criteria); 
    }
}