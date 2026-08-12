<?php

namespace Alpha\Mappers\Observers;

use Alpha\Mappers\Observers\OrderObserverInterface;
use Alpha\Model\Domain\Entities\Order;

/**
 * OrderEmailObserver - Dispara e-mails de confirmação após a criação do pedido.
 */
class OrderEmailObserver implements OrderObserverInterface
{
    private $mailer;

    public function __construct($mailer = null)
    {
        $this->mailer = $mailer;
    }

    public function update(Order $order): void
    {
        // Lógica de envio de e-mail (Ex: Usando Opencart Mail ou Alpha Mail Service)
        // $this->mailer->send($order->getEmail(), 'Pedido Confirmado #' . $order->getId());
        
        // Para depuração na Alpha Engine:
        error_log("Alpha Engine: Disparando e-mail para o pedido ID " . $order->getId());
    }
}