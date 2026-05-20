<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use DateTimeImmutable;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerReward
 * Pontos de fidelidade acumulados pelo cliente.
 * 
 * @Table(name="customer_reward")
 */
class CustomerReward extends BaseEntity
{
    private int $customerId = 0;
    private int $orderId = 0;
    private string $description = '';
    private int $points = 0;
    private ?DateTimeImmutable $dateAdded = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    /**
     * Apontamentos Técnicos:
     * 1. Vínculo Transacional: O orderId permite rastrear qual compra gerou os pontos.
     * 2. Inteiro Estrito: Pontos são sempre inteiros na Alpha Engine para evitar divisões fracionadas.
     */

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

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
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