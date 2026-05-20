<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use DateTimeImmutable;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerTransaction
 * Créditos e débitos financeiros na conta do cliente (Saldo).
 * 
 * @Table(name="customer_transaction")
 */
class CustomerTransaction extends BaseEntity
{
    private int $orderId = 0;
    private string $description = '';
    private float $amount = 0.0000;
    private ?DateTimeImmutable $dateAdded = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    /**
     * Apontamentos Técnicos:
     * 1. Precisão Financeira: O campo 'amount' é float para suportar centavos no saldo.
     * 2. Auditoria: Fundamental para processos de estorno ou créditos de bonificação.
     */

    public function getCustomerId(): int
    {
        return $this->customer ? (int)$this->customer->getId() : 0;
    }

    public function setCustomerId(int $customerId): self
    {
        if (!$this->customer) {
            $this->customer = new Customer();
        }
        $this->customer->setId($customerId);
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

    public function getDateAdded(): ?DateTimeImmutable
    {
        return $this->dateAdded;
    }

    public function setDateAdded(?DateTimeImmutable $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }
}