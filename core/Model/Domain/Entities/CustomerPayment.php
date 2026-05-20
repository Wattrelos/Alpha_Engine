<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerPayment
 * Armazena métodos de pagamento salvos pelo cliente (Cofre/Vault).
 * 
 * @Table(name="customer_payment")
 */
class CustomerPayment extends BaseEntity
{
    private int $customerId = 0;
    private string $name = '';
    private string $image = '';
    private string $type = '';
    private string $description = '';
    private array $data = [];

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    /**
     * Apontamentos Técnicos:
     * 1. Campo Data (Array): Armazena tokens e metadados da operadora de forma serializada.
     * 2. Abstração: O campo 'type' identifica o gateway de pagamento responsável (ex: 'stripe', 'paypal').
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }
}