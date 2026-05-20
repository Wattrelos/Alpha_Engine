<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * WishlistMapper - Gerencia a lista de desejos dos clientes (Alpha Engine).
 */
class WishlistMapper extends BaseMapper {

    protected string $tableName = 'customer_wishlist';

    /**
     * Obtém o total de itens na lista de desejos de um cliente.
     * 
     * @param int $customer_id
     * @return int
     */
    public function getTotalWishlist(int $customer_id): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'customer_wishlist')
            ->where("customer_id = ?", [$customer_id]);
        
        return $this->dao->executeCount($query);
    }
}