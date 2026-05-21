<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar a lógica de Países (Countries)
 */
class CountryMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém um país específico pelo ID
     * 
     * @param int $country_id
     * @return array
     */
    public function getCountry(int $country_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'country', 'c')
            ->where("c.id = ?", [$country_id])
            ->where("c.status = ?", [1])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista todos os países ativos
     * 
     * @return array
     */
    public function getCountries(): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'country', 'c')
            ->where("c.status = ?", [1])
            ->orderBy("c.name", "ASC")
            ->select('*');

        return $this->dao->executeQuery($query);
    }
}