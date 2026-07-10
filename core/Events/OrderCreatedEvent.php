<?php

namespace Alpha\Events;

use Alpha\Model\Domain\DTOs\OrderDataDTO;

/**
 * OrderCreatedEvent - Evento disparado no fechamento bem-sucedido de um pedido.
 */
class OrderCreatedEvent implements EventInterface
{
    private int $orderId;
    private ?OrderDataDTO $orderDto;

    /**
     * @param int $orderId
     * @param OrderDataDTO|null $orderDto
     */
    public function __construct(int $orderId, ?OrderDataDTO $orderDto = null)
    {
        $this->orderId = $orderId;
        $this->orderDto = $orderDto;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'order.created';
    }

    /**
     * Retorna o ID do pedido criado.
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * Retorna o DTO de dados estruturados do pedido, se houver.
     *
     * @return OrderDataDTO|null
     */
    public function getOrderDto(): ?OrderDataDTO
    {
        return $this->orderDto;
    }
}
