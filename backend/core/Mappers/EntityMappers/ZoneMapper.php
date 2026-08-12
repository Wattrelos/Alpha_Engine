<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Zone;

/**
 * Mapper para gerenciar a lógica de Zonas/Estados (Zones)
 * Refatorado (Alpha Engine): Estende BaseMapper para hidratação automática.
 */
class ZoneMapper extends BaseMapper {
    
    protected string $tableName = 'zone';
    protected string $entityClass = Zone::class;

    /**
     * Obtém uma zona específica pelo ID
     * 
     * @param int $zone_id
     * @return Zone|null
     */
    public function getZone(int $zone_id): ?Zone {
        $zone = $this->findById($zone_id);
        return ($zone && $zone->getStatus()) ? $zone : null;
    }

    /**
     * Lista todas as zonas de um país específico
     * 
     * @param int $country_id
     * @return Zone[]
     */
    public function getZonesByCountryId(int $country_id): array {
        return $this->search(
            ['countryId' => $country_id, 'status' => 1]
        );
    }

    /**
     * Obtém o total de zonas de um país (Útil para validações)
     * 
     * @param int $country_id
     * @return int
     */
    public function getTotalZonesByCountryId(int $country_id): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . $this->tableName, 'z')
            ->where("z.country_id = ?", [$country_id])
            ->where("z.status = ?", [1]);

        return $this->dao->executeCount($query);
    }
}