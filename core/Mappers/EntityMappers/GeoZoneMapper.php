<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\DataAccessObject;

class GeoZoneMapper
{
    private DataAccessObject $dao;

    public function __construct(DataAccessObject $dao)
    {
        $this->dao = $dao;
    }

    public function findAll(): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'geo_zone')
            ->select('geo_zone_id, name')
            ->orderBy('name', 'ASC');

        return $this->dao->executeQuery($builder);
    }

    public function checkAddressInZone(int $geoZoneId, int $countryId, int $zoneId): bool
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'zone_to_geo_zone')
            ->where('geo_zone_id = ? AND country_id = ? AND (zone_id = ? OR zone_id = 0)', [$geoZoneId, $countryId, $zoneId])
            ->select('zone_to_geo_zone_id')
            ->limit(1);

        $result = $this->dao->executeQuery($builder);

        return !empty($result);
    }
}