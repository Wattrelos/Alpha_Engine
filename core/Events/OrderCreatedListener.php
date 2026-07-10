<?php

namespace Alpha\Events;

/**
 * OrderCreatedListener - Observer concreto que reage ao evento de criação de pedido
 * e encaminha o payload JSON simplificado para a fila do RabbitMQ.
 */
class OrderCreatedListener implements ListenerInterface
{
    private QueueService $queueService;

    /**
     * @param QueueService $queueService
     */
    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EventInterface $event): void
    {
        if ($event instanceof OrderCreatedEvent) {
            $orderDto = $event->getOrderDto();
            
            // Monta o payload simplificado e auditável do pedido
            $payload = [
                'event' => $event->getName(),
                'order_id' => $event->getOrderId(),
                'timestamp' => date('c'),
                'total' => $orderDto ? (float)$orderDto->get('total') : 0.0,
                'email' => $orderDto ? (string)$orderDto->get('email') : '',
                'firstname' => $orderDto ? (string)$orderDto->get('firstname') : '',
                'lastname' => $orderDto ? (string)$orderDto->get('lastname') : '',
                'telephone' => $orderDto ? (string)$orderDto->get('telephone') : '',
                'payment_method' => $orderDto ? (string)$orderDto->get('payment_method') : '',
                'shipping_method' => $orderDto ? (string)$orderDto->get('shipping_method') : ''
            ];

            // Publica os dados do pedido na fila "order.created"
            $this->queueService->publish('order.created', $payload);
        }
    }
}
