<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * LocationMapper - Gerencia as localizações físicas da loja.
 */
class LocationMapper extends BaseMapper
{
    protected string $tableName = 'location';

    /**
     * Obtém uma localização específica.
     */
    public function getLocation(int $location_id): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'location')
            ->where("id = ?", [$location_id])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }
}