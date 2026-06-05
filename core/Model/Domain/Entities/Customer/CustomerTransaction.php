<?php

namespace Alpha\Model\Domain\Entities\Customer;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Entities\Order;

/**
 * Entidade CustomerTransaction
 * Representa o histórico de movimentações financeiras (Saldo/Créditos em Loja) de um cliente.
 * 
 * @Table(name="customer_transaction")
 */
class CustomerTransaction extends BaseEntity
{
    private int $customerId = 0;
    private int $orderId = 0;
    private string $description = '';
    private float $amount = 0.0;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { clone $this->customer = $customer; return $this; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { clone $this->order = $order; return $this; }
}