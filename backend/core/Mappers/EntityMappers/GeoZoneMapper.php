<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Geo\Zone;

/**
 * Mapper para gerenciar a lógica de Estados (Geo Zone)
 */
class GeoZoneMapper extends BaseMapper {
    
    protected string $tableName = 'geo_zones';
    protected string $entityClass = Zone::class;

    /**
     * Lista todas as zonas de um país
     * 
     * @param int $country_id
     * @return Zone[]
     */
    public function getZonesByCountryId(int $country_id): array {
        return $this->search(['countryId' => $country_id]);
    }
}