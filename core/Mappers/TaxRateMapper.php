<?php

namespace Alpha\Mappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar as alíquotas de impostos (Tax Rates)
 */
class TaxRateMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém uma alíquota de imposto específica por ID
     * 
     * @param int $tax_rate_id
     * @return array
     */
    public function getTaxRate(int $tax_rate_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'tax_rate', 'tr')
            ->where("tr.id = ?", [$tax_rate_id])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista todas as alíquotas de impostos cadastradas
     * 
     * @param array $data Filtros opcionais (sort, order, start, limit)
     * @return array
     */
    public function getTaxRates(array $data = []): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'tax_rate', 'tr')
            ->orderBy($data['sort'] ?? 'tr.name', $data['order'] ?? 'ASC');

        if (isset($data['start']) || isset($data['limit'])) {
            $query->limit((int)($data['limit'] ?? 20))
                  ->offset((int)($data['start'] ?? 0));
        }

        $query->select('*');

        return $this->dao->executeQuery($query);
    }
}