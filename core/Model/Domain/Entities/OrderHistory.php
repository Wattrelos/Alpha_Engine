<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade OrderHistory - Registra a linha do tempo de processamento de um pedido.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Trilha de Vendas: Registro cronológico de mudanças de status e comunicações com o cliente.
 * - Tipagem PHP 8.4: Uso de bool para notificação e int para chaves estrangeiras.
 * - Mapeamento Relacional: Vinculação ManyToOne com Order e OrderStatus para consistência de dados.
 * - Auditoria: Campo de comentário para registro de observações de operadores ou gateways de pagamento.
 */
class OrderHistory extends BaseEntity
{
    private int $orderId = 0;
    private int $orderStatusId = 0;
    private bool $notify = false;
    private string $comment = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    #[ManyToOne(targetEntity: OrderStatus::class, foreignKey: 'orderStatusId')]
    private ?OrderStatus $orderStatus = null;

    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $id): self { $this->orderId = $id; return $this; }

    public function getOrderStatusId(): int { return $this->orderStatusId; }
    public function setOrderStatusId(int $id): self { $this->orderStatusId = $id; return $this; }

    public function isNotify(): bool { return $this->notify; }
    public function setNotify(bool|int $value): self { $this->notify = (bool)$value; return $this; }

    public function getComment(): string { return $this->comment; }
    public function setComment(string $comment): self { $this->comment = $comment; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    // Relacionamentos para Hidratação via DAO

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { $this->order = $order; return $this; }

    public function getOrderStatus(): ?OrderStatus { return $this->orderStatus; }
    public function setOrderStatus(?OrderStatus $status): self { $this->orderStatus = $status; return $this; }
}