<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use DateTimeImmutable;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerHistory
 * Registro de interações e anotações manuais ou automáticas sobre o cliente.
 * 
 * @Table(name="customer_history")
 */
class CustomerHistory extends BaseEntity
{
    private int $customerId = 0;
    private string $comment = '';
    private ?DateTimeImmutable $dateAdded = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    /**
     * Apontamentos Técnicos:
     * 1. Rastreabilidade: Essencial para o suporte ao cliente visualizar o histórico de contatos.
     * 2. Imutabilidade: Uma vez registrado, o comentário serve como snapshot do evento.
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

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
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

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
        if ($customer) {
            $this->customerId = $customer->getId();
        }
        return $this;
    }
}