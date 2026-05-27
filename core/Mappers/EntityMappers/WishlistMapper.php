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
        $sql = "SELECT * FROM `" . $this->getFullTableName() . "` WHERE `customer_id` = :customer_id AND `store_id` = :store_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'customer_id' => $customer_id,
            'store_id'    => $store_id
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
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
        if ($store_id === 0 && $this->registry !== null) {
            $store_id = (int)$this->registry->get('config')->get('config_store_id');
        }

        $sql = "DELETE FROM `" . $this->getFullTableName() . "` WHERE `customer_id` = :customer_id AND `store_id` = :store_id";
        $params = [
            'customer_id' => $customer_id,
            'store_id'    => $store_id
        ];

        if ($product_id > 0) {
            $sql .= " AND `product_id` = :product_id";
            $params['product_id'] = $product_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Obtém o total de itens na lista de desejos de um cliente.
     * 
     * @param int $customer_id
     * @param int $store_id
     * @return int
     */
    public function getTotalWishlist(int $customer_id, int $store_id = 0): int {
        $sql = "SELECT COUNT(*) AS `total` FROM `" . $this->getFullTableName() . "` WHERE `customer_id` = :customer_id AND `store_id` = :store_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'customer_id' => $customer_id,
            'store_id'    => $store_id
        ]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }
}