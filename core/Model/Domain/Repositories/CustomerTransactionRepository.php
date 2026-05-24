<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\CustomerTransaction;

/**
 * CustomerTransactionRepository
 * Gerencia os fundos/carteira e histórico financeiro interno do cliente.
 */
class CustomerTransactionRepository extends AbstractRepository
{
    /**
     * Retorna todo o histórico de transações de um cliente em ordem cronológica (mais recentes primeiro).
     *
     * @param int $customerId
     * @return CustomerTransaction[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->mapper->search(['customerId' => $customerId], ['dateAdded' => 'DESC']);
    }

    /**
     * Calcula e retorna o saldo atual disponível na conta do cliente.
     * O cálculo é feito agregando os valores das transações (créditos são positivos, débitos negativos).
     *
     * @param int $customerId
     * @return float
     */
    public function getBalance(int $customerId): float
    {
        $transactions = $this->findByCustomerId($customerId);
        $balance = 0.0;
        foreach ($transactions as $transaction) { $balance += $transaction->getAmount(); }
        return $balance;
    }
}