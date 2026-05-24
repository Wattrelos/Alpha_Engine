<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\OrderReturn;

/**
 * OrderReturnRepository
 * Centraliza o acesso aos dados das devoluções solicitadas pelos clientes.
 */
class OrderReturnRepository extends AbstractRepository
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
}