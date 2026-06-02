<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\InterfaceEntity;

/**
 * SubscriptionRepository
 * Gerencia assinaturas e pagamentos recorrentes do cliente.
 */
class SubscriptionRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Legacy Bridge: Retorna dados de uma assinatura específica.
     */
    public function getSubscription(int $subscription_id): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription` WHERE `subscription_id` = '" . (int)$subscription_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "'");
        
        if ($query->num_rows) {
            $query->row['shipping_method'] = $query->row['shipping_method'] ? json_decode($query->row['shipping_method'], true) : [];
            $query->row['payment_method'] = $query->row['payment_method'] ? json_decode($query->row['payment_method'], true) : [];
            return $query->row;
        }

        return [];
    }

    /**
     * Legacy Bridge: Retorna todas as assinaturas do cliente para listagem.
     */
    public function getSubscriptions(int $start = 0, int $limit = 20): array
    {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 20;
        }
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription` WHERE `customer_id` = '" . (int)$this->customer->getId() . "' ORDER BY `subscription_id` DESC LIMIT " . (int)$start . "," . (int)$limit);
        return $query->rows;
    }

    /**
     * Legacy Bridge: Retorna o total de assinaturas da conta.
     */
    public function getTotalSubscriptions(): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "subscription` WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");
        return (int)$query->row['total'];
    }

    /**
     * Legacy Bridge: Conta o total de produtos atrelados a uma assinatura (Normalmente 1).
     */
    public function getTotalProducts(int $subscription_id): int
    {
        return 1;
    }

    /**
     * Legacy Bridge: Retorna as informações do produto atrelado à assinatura.
     */
    public function getProducts(int $subscription_id): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription` WHERE `subscription_id` = '" . (int)$subscription_id . "'");
        if ($query->num_rows) {
            $query->row['subscription_product_id'] = $query->row['order_product_id'];
            return [$query->row];
        }
        return [];
    }

    /**
     * Legacy Bridge: Retorna as opções selecionadas para aquele produto em específico.
     */
    public function getOptions(int $product_id, int $subscription_product_id): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE `order_product_id` = '" . (int)$subscription_product_id . "'");
        return $query->rows;
    }

    /**
     * Legacy Bridge: Adiciona log ao histórico da assinatura.
     */
    public function addHistory(int $subscription_id, int $subscription_status_id, string $comment = '', bool $notify = false): void
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `subscription_status_id` = '" . (int)$subscription_status_id . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_history` SET `subscription_id` = '" . (int)$subscription_id . "', `subscription_status_id` = '" . (int)$subscription_status_id . "', `comment` = '" . $this->db->escape($comment) . "', `notify` = '" . (int)$notify . "', `date_added` = NOW()");
    }

    /**
     * Legacy Bridge: Retorna a linha do tempo (histórico) da assinatura.
     */
    public function getHistories(int $subscription_id, int $start = 0, int $limit = 10): array
    {
        if ($start < 0) { $start = 0; }
        if ($limit < 1) { $limit = 10; }
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription_history` WHERE `subscription_id` = '" . (int)$subscription_id . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);
        return $query->rows;
    }

    /**
     * Legacy Bridge: Total de alterações no histórico da assinatura.
     */
    public function getTotalHistories(int $subscription_id): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "subscription_history` WHERE `subscription_id` = '" . (int)$subscription_id . "'");
        return (int)$query->row['total'];
    }

    /**
     * Legacy Bridge: Localiza uma assinatura ativa a partir dos dados do Pedido e Produto.
     */
    public function getProductByOrderProductId(int $order_id, int $order_product_id): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription` WHERE `order_id` = '" . (int)$order_id . "' AND `order_product_id` = '" . (int)$order_product_id . "'");
        return $query->row;
    }

    // --- Implementações Obrigatórias da Interface BaseRepositoryInterface ---

    public function find(int $id): ?InterfaceEntity { return null; }

    public function findAll(): array { return []; }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }

    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}
