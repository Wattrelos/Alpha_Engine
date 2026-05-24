<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\CustomerReward;

/**
 * CustomerRewardRepository
 * Gerencia o programa de fidelidade e extração de pontos do cliente.
 */
class CustomerRewardRepository extends AbstractRepository
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
}