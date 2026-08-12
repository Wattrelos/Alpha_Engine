<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Geo\City;

/**
 * Mapper para gerenciar a lógica de Cidades (Geo City)
 */
class GeoCityMapper extends BaseMapper {
    
    protected string $tableName = 'geo_cities';
    protected string $entityClass = City::class;

    /**
     * Lista todas as cidades de um estado
     * 
     * @param int $zone_id
     * @return City[]
     */
    public function getCitiesByZoneId(int $zone_id): array {
        return $this->search(['zoneId' => $zone_id]);
    }
}
