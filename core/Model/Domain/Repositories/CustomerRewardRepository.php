<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\CustomerReward;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * CustomerRewardRepository
 * Gerencia o programa de fidelidade e extração de pontos do cliente.
 */
class CustomerRewardRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Retorna todo o histórico de ganho e uso de pontos de um cliente.
     *
     * @param int $customerId
     * @return CustomerReward[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->mapper->search(['customerId' => $customerId], ['dateAdded' => 'DESC']);
    }

    /**
     * Calcula e retorna o total de pontos ativos (não gastos) do cliente.
     *
     * @param int $customerId
     * @return int
     */
    public function getTotalPoints(int $customerId): int
    {
        $rewards = $this->findByCustomerId($customerId);
        $points = 0;
        foreach ($rewards as $reward) { $points += $reward->getPoints(); }
        return $points;
    }

    /**
     * Legacy Bridge: Retorna o histórico formatado para a visão de Recompensas.
     *
     * @param int $customerId
     * @param array $data
     * @return array
     */
    public function getRewards(int $customerId, array $data = []): array
    {
        $start = (int)($data['start'] ?? 0);
        $limit = (int)($data['limit'] ?? 20);

        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 20;
        }

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_reward` WHERE customer_id = '" . (int)$customerId . "' ORDER BY date_added DESC LIMIT " . $start . "," . $limit);

        return $query->rows;
    }

    /**
     * Legacy Bridge: Retorna a quantidade total de entradas de recompensas.
     *
     * @param int $customerId
     * @return int
     */
    public function getTotalRewards(int $customerId): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_reward` WHERE customer_id = '" . (int)$customerId . "'");
        return (int)$query->row['total'];
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
        $results = $this->mapper->search($criteria);
        return $results[0] ?? null;
    }
}