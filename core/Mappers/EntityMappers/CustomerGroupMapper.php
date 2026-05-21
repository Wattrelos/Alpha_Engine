<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar grupos de clientes e suas regras
 */
class CustomerGroupMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém um grupo de cliente específico
     * 
     * @param int $customer_group_id
     * @param int $language_id
     * @return array
     */
    public function getCustomerGroup(int $customer_group_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'customer_group', 'cg')
            ->leftJoin(DB_PREFIX . 'customer_group_description', 'cgd', 'cg.id = cgd.customer_group_id')
            ->where("cg.id = ?", [$customer_group_id])
            ->where("cgd.language_id = ?", [$language_id])
            ->select('DISTINCT *');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista todos os grupos de clientes disponíveis
     * 
     * @param int $language_id
     * @return array
     */
    public function getCustomerGroups(int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'customer_group', 'cg')
            ->leftJoin(DB_PREFIX . 'customer_group_description', 'cgd', 'cg.id = cgd.customer_group_id')
            ->where("cgd.language_id = ?", [$language_id])
            ->orderBy("cg.sort_order", "ASC");

        return $this->dao->executeQuery($query);
    }
}