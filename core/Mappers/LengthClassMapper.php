<?php

namespace Alpha\Mappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar classes de comprimento (Length Classes)
 */
class LengthClassMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém uma classe de comprimento específica por ID e Idioma
     */
    public function getLengthClass(int $length_class_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'length_class', 'lc')
            ->leftJoin(DB_PREFIX . 'length_class_description', 'lcd', 'lc.id = lcd.length_class_id')
            ->where("lc.id = ?", [$length_class_id])
            ->where("lcd.language_id = ?", [$language_id])
            ->select('DISTINCT *');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista todas as classes de comprimento para um determinado idioma
     */
    public function getLengthClasses(int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'length_class', 'lc')
            ->leftJoin(DB_PREFIX . 'length_class_description', 'lcd', 'lc.id = lcd.length_class_id')
            ->where("lcd.language_id = ?", [$language_id])
            ->select('*');

        return $this->dao->executeQuery($query);
    }
}