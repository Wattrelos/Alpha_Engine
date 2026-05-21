<?php

namespace Alpha\Model\Domain\Observers;

use Alpha\Model\Domain\Entities\Order;

/**
 * Interface OrderObserverInterface - Define o contrato para ações pós-persistência de pedidos.
 */
interface OrderObserverInterface
{
    /**
     * Executado sempre que um pedido é salvo com sucesso.
     */
    public function update(Order $order): void;
}