<?php

namespace Alpha\Mappers\EntityMappers;

use Opencart\System\Engine\Registry;

/**
 * Class CartMapper
 * 
 * Gerencia as operações de banco de dados (CRUD) exclusivas da tabela de carrinho,
 * isolando o SQL da camada de domínio (CartRepository).
 */
class CartMapper
{
    private object $db;

    public function __construct(Registry $registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * Limpa carrinhos abandonados de visitantes baseando-se no tempo de expiração da sessão.
     */
    public function deleteExpiredCarts(int $storeId, int $expireSeconds): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "cart` 
            WHERE `store_id` = '" . (int)$storeId . "' 
            AND `customer_id` = '0' 
            AND `date_added` < DATE_SUB(NOW(), INTERVAL " . (int)$expireSeconds . " SECOND)");
    }

    /**
     * Mescla o carrinho salvo do cliente (banco) com os itens que ele 
     * adicionou na sessão atual (visitante) antes de fazer o login.
     */
    public function mergeCustomerCart(int $customerId, string $sessionId, int $storeId): void
    {
        // 1. Atualiza o ID da sessão nos itens antigos salvos pelo cliente
        $this->db->query("UPDATE `" . DB_PREFIX . "cart` 
            SET `session_id` = '" . $this->db->escape($sessionId) . "', `date_added` = NOW() 
            WHERE `store_id` = '" . (int)$storeId . "' AND `customer_id` = '" . (int)$customerId . "'");

        // 2. Associa os novos itens adicionados como visitante (customer_id = 0) ao cliente recém-logado
        $this->db->query("UPDATE `" . DB_PREFIX . "cart` 
            SET `customer_id` = '" . (int)$customerId . "', `date_added` = NOW() 
            WHERE `store_id` = '" . (int)$storeId . "' AND `customer_id` = '0' AND `session_id` = '" . $this->db->escape($sessionId) . "'");
    }

    /**
     * Busca todos os itens do carrinho com base no contexto (Logado ou Visitante).
     */
    public function findAllByContext(int $customerId, string $sessionId, int $storeId): array
    {
        if ($customerId) {
            // Traz apenas itens salvos na conta do cliente
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cart` 
                WHERE `customer_id` = '" . (int)$customerId . "' AND `store_id` = '" . (int)$storeId . "'");
        } else {
            // Traz apenas itens da sessão do visitante
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cart` 
                WHERE `customer_id` = '0' AND `session_id` = '" . $this->db->escape($sessionId) . "' AND `store_id` = '" . (int)$storeId . "'");
        }

        return $query->rows;
    }

    /**
     * Atualiza a quantidade de um item existente no carrinho.
     */
    public function updateQuantity(int $cartId, int $quantity): void
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "cart` 
            SET `quantity` = '" . (int)$quantity . "' 
            WHERE `cart_id` = '" . (int)$cartId . "'");
    }

    /**
     * Remove um item específico do carrinho.
     */
    public function delete(int $cartId): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "cart` 
            WHERE `cart_id` = '" . (int)$cartId . "'");
    }

    /**
     * Esvazia completamente o carrinho do usuário atual (usado após a confirmação do pedido).
     */
    public function clearByContext(int $customerId, string $sessionId, int $storeId): void
    {
        if ($customerId) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "cart` 
                WHERE `customer_id` = '" . (int)$customerId . "' AND `store_id` = '" . (int)$storeId . "'");
        } else {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "cart` 
                WHERE `customer_id` = '0' AND `session_id` = '" . $this->db->escape($sessionId) . "' AND `store_id` = '" . (int)$storeId . "'");
        }
    }

    /**
     * Insere um novo item no carrinho de forma bruta (inserção limpa).
     * (Nota: A validação para não duplicar itens e somar a quantidade será tratada pelo Repository)
     */
    public function insert(int $customerId, string $sessionId, int $storeId, int $productId, int $quantity, string $optionHash, int $subscriptionPlanId = 0): void
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "cart` 
            SET `customer_id` = '" . (int)$customerId . "', `session_id` = '" . $this->db->escape($sessionId) . "', 
            `store_id` = '" . (int)$storeId . "', `product_id` = '" . (int)$productId . "', 
            `subscription_plan_id` = '" . (int)$subscriptionPlanId . "', `option` = '" . $this->db->escape($optionHash) . "', 
            `quantity` = '" . (int)$quantity . "', `date_added` = NOW()");
    }
}