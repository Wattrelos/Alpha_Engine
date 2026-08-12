<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar classes de impostos (Tax Classes)
 */
class TaxClassMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém uma classe de imposto específica por ID
     * 
     * @param int $tax_class_id
     * @return array
     */
    public function getTaxClass(int $tax_class_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'tax_class', 'tc')
            ->where("tc.id = ?", [$tax_class_id])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista todas as classes de impostos cadastradas
     * 
     * @return array
     */
    public function getTaxClasses(): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'tax_class', 'tc')
            ->orderBy("tc.title", "ASC")
            ->select('*');

        return $this->dao->executeQuery($query);
    }
}