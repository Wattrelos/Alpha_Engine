<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * LayoutMapper
 * 
 * Gerencia a persistência e resolução de Layouts da loja.
 */
class LayoutMapper extends BaseMapper
{
    protected string $tableName = 'layout';

    /**
     * Resolve o ID do Layout baseado na Rota e na Loja atual.
     * Suporta a lógica de wildcards (curingas) nativa do OpenCart (ex: product/%).
     * 
     * @param string $route Ex: 'product/product', 'checkout/cart'
     * @param int $storeId ID da loja atual
     * @return int
     */
    public function findIdByRoute(string $route, int $storeId = 0): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'layout_route')
            ->where("? LIKE `route`", [$route])
            ->where("`store_id` = ?", [$storeId])
            ->orderBy("`route`", "DESC")
            ->select('`layout_id`')
            ->limit(1);
            
        $results = $this->dao->executeQuery($query);

        return (int)($results[0]['layout_id'] ?? 0);
    }
}