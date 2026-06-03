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
     * Obtém a lista completa de desejos de um cliente para uma loja específica.
     *
     * @param int $customer_id
     * @param int $store_id
     * @return array
     */
    public function getWishlist(int $customer_id, int $store_id): array {
        $query = (new QueryBuilder())
            ->select('*')
            ->from($this->getFullTableName())
            ->where("customer_id = ?", [$customer_id])
            ->where("store_id = ?", [$store_id]);

        return $this->dao->executeQuery($query);
    }

    /**
     * Adiciona um produto à lista de desejos, garantindo que não haja duplicidade.
     *
     * @param int $customer_id
     * @param int $product_id
     * @param int $store_id
     * @return void
     */
    public function addWishlist(int $customer_id, int $product_id, int $store_id): void {
        // Primeiro remove para evitar duplicados
        $this->deleteWishlist($customer_id, $product_id, $store_id);

        $sql = "INSERT INTO `" . $this->getFullTableName() . "` SET `customer_id` = :customer_id, `store_id` = :store_id, `product_id` = :product_id, `date_added` = NOW()";
        $this->dao->executeRawSQL($sql, [
            'customer_id' => $customer_id,
            'store_id'    => $store_id,
            'product_id'  => $product_id
        ]);
    }

    /**
     * Remove um ou todos os produtos da lista de desejos de um cliente.
     *
     * @param int $customer_id
     * @param int $product_id
     * @param int $store_id
     * @return void
     */
    public function deleteWishlist(int $customer_id, int $product_id, int $store_id = 0): void {
        // Alpha Engine: Removido o acoplamento com o Registry legado.
        // O store_id default (0) atende à loja principal. Em ambientes multi-loja,
        // a Action chamadora (Controller) deve prover o store_id explicitamente.

        $query = (new QueryBuilder())
            ->delete($this->getFullTableName())
            ->where("customer_id = ?", [$customer_id])
            ->where("store_id = ?", [$store_id]);

        if ($product_id > 0) {
            $query->where("product_id = ?", [$product_id]);
        }

        $this->dao->execute($query);
    }

    /**
     * Obtém o total de itens na lista de desejos de um cliente.
     * 
     * @param int $customer_id
     * @param int $store_id
     * @return int
     */
    public function getTotalWishlist(int $customer_id, int $store_id = 0): int {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("customer_id = ?", [$customer_id])
            ->where("store_id = ?", [$store_id]);

        return $this->dao->executeCount($query);
    }
}