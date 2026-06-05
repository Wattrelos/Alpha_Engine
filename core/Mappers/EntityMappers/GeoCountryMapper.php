<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Geo\Country;

/**
 * Mapper para gerenciar a lógica de Países (Geo Country)
 */
class GeoCountryMapper extends BaseMapper {
    
    protected string $tableName = 'geo_countries';
    protected string $entityClass = Country::class;

    /**
     * Lista todos os países ativos
     * 
     * @return Country[]
     */
    public function getCountries(): array {
        return $this->search(['isActive' => 1]);
    }
}
