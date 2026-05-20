<?php

namespace Alpha\Mappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar classes de peso (Weight Classes)
 */
class WeightClassMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém uma classe de peso específica por ID e Idioma
     */
    public function getWeightClass(int $weight_class_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'weight_class', 'wc')
            ->leftJoin(DB_PREFIX . 'weight_class_description', 'wcd', 'wc.id = wcd.weight_class_id')
            ->where("wc.id = ?", [$weight_class_id])
            ->where("wcd.language_id = ?", [$language_id])
            ->select('DISTINCT *');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista todas as classes de peso para um determinado idioma
     */
    public function getWeightClasses(int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'weight_class', 'wc')
            ->leftJoin(DB_PREFIX . 'weight_class_description', 'wcd', 'wc.id = wcd.weight_class_id')
            ->where("wcd.language_id = ?", [$language_id])
            ->select('*');

        return $this->dao->executeQuery($query);
    }
}