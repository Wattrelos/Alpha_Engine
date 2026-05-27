<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\OrderReturn;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * OrderReturnRepository
 * Centraliza o acesso aos dados das devoluções solicitadas pelos clientes.
 */
class OrderReturnRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Busca todo o histórico de devoluções de um cliente específico.
     *
     * @param int $customerId
     * @return OrderReturn[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->mapper->search(
            ['customerId' => $customerId],
            ['dateAdded' => 'DESC']
        );
    }

    /**
     * Busca as devoluções atreladas a um pedido específico.
     *
     * @param int $orderId
     * @return OrderReturn[]
     */
    public function findByOrderId(int $orderId): array
    {
        return $this->mapper->search(['orderId' => $orderId]);
    }

    /**
     * Legacy Bridge: Adiciona uma nova solicitação de devolução.
     */
    public function addReturn(array $data): void
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "return` SET order_id = '" . (int)$data['order_id'] . "', customer_id = '" . (int)$this->customer->getId() . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', product = '" . $this->db->escape($data['product']) . "', model = '" . $this->db->escape($data['model']) . "', quantity = '" . (int)$data['quantity'] . "', opened = '" . (empty($data['opened']) ? 0 : 1) . "', return_reason_id = '" . (int)$data['return_reason_id'] . "', return_status_id = '" . (int)$this->config->get('config_return_status_id') . "', comment = '" . $this->db->escape($data['comment']) . "', date_ordered = '" . $this->db->escape($data['date_ordered']) . "', date_added = NOW(), date_modified = NOW()");
    }

    /**
     * Legacy Bridge: Retorna os dados completos de uma devolução.
     */
    public function getReturn(int $return_id): array
    {
        $query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, r.email, r.telephone, r.product, r.model, r.quantity, r.opened, (SELECT rr.name FROM " . DB_PREFIX . "return_reason rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = '" . (int)$this->config->get('config_language_id') . "') AS reason, (SELECT ra.name FROM " . DB_PREFIX . "return_action ra WHERE ra.return_action_id = r.return_action_id AND ra.language_id = '" . (int)$this->config->get('config_language_id') . "') AS action, (SELECT rs.name FROM " . DB_PREFIX . "return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status, r.comment, r.date_ordered, r.date_added, r.date_modified FROM `" . DB_PREFIX . "return` r WHERE r.return_id = '" . (int)$return_id . "' AND r.customer_id = '" . (int)$this->customer->getId() . "'");

        return $query->row;
    }

    /**
     * Legacy Bridge: Lista todas as devoluções do cliente atual.
     */
    public function getReturns(int $start = 0, int $limit = 20): array
    {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 20;
        }

        $query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, rs.name as status, r.date_added FROM `" . DB_PREFIX . "return` r LEFT JOIN " . DB_PREFIX . "return_status rs ON (r.return_status_id = rs.return_status_id) WHERE r.customer_id = '" . (int)$this->customer->getId() . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.return_id DESC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    /**
     * Legacy Bridge: Conta o total de devoluções atreladas ao cliente.
     */
    public function getTotalReturns(): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "return` WHERE customer_id = '" . (int)$this->customer->getId() . "'");
        return $query->row['total'];
    }

    /**
     * Legacy Bridge: Retorna o log (histórico) de status de uma devolução específica.
     */
    public function getHistories(int $return_id, int $start = 0, int $limit = 20): array
    {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 20;
        }

        $query = $this->db->query("SELECT rh.date_added, rs.name AS status, rh.comment FROM " . DB_PREFIX . "return_history rh LEFT JOIN " . DB_PREFIX . "return_status rs ON rh.return_status_id = rs.return_status_id WHERE rh.return_id = '" . (int)$return_id . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY rh.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    /**
     * Legacy Bridge: Conta o total de atualizações no histórico de uma devolução.
     */
    public function getTotalHistories(int $return_id): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "return_history WHERE return_id = '" . (int)$return_id . "'");
        return $query->row['total'];
    }

    // --- Implementações Obrigatórias da Interface BaseRepositoryInterface ---

    public function find(int $id): ?InterfaceEntity
    {
        return $this->mapper->findById($id);
    }

    public function findAll(): array
    {
        return $this->mapper->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapper->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $res = $this->mapper->search($criteria);
        return $res[0] ?? null;
    }
}