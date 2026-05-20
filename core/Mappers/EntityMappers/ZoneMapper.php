<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar a lógica de Zonas/Estados (Zones)
 */
class ZoneMapper extends BaseMapper {

    protected string $tableName = 'zone';

    /**
     * Obtém uma zona específica pelo ID
     * 
     * @param int $zone_id
     * @return array
     */
    public function getZone(int $zone_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'zone', 'z')
            ->where("z.id = ?", [$zone_id])
            ->where("z.status = ?", [1])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista todas as zonas de um país específico
     * 
     * @param int $country_id
     * @return array
     */
    public function getZonesByCountryId(int $country_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'zone', 'z')
            ->where("z.country_id = ?", [$country_id])
            ->where("z.status = ?", [1])
            ->orderBy("z.name", "ASC")
            ->select('*');

        return $this->dao->executeQuery($query);
    }

    /**
     * Obtém o total de zonas de um país (Útil para validações)
     * 
     * @param int $country_id
     * @return int
     */
    public function getTotalZonesByCountryId(int $country_id): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'zone', 'z')
            ->where("z.country_id = ?", [$country_id])
            ->where("z.status = ?", [1]);

        return $this->dao->executeCount($query);
    }
}