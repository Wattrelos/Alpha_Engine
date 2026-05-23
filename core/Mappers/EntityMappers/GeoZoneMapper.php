<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\DataAccessObject;

class GeoZoneMapper
{
    private DataAccessObject $dao;

    public function __construct()
    {
        $this->dao = new DataAccessObject();
    }

    public function findAll(): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'geo_zone')
            ->select('geo_zone_id, name')
            ->orderBy('name', 'ASC');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Alpha Engine: Batch Loading de Zonas Geográficas (Fim do N+1 em fretes e impostos)
     * Retorna todos os IDs de Geo Zones aos quais este endereço pertence de uma só vez.
     */
    public function getValidGeoZoneIdsForAddress(int $countryId, int $zoneId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'zone_to_geo_zone')
            ->where('country_id = ? AND (zone_id = ? OR zone_id = 0)', [$countryId, $zoneId])
            ->select('geo_zone_id');

        $result = $this->dao->executeQuery($builder);

        return array_map('intval', array_column($result, 'geo_zone_id'));
    }
}