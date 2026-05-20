<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Notification - Gerencia avisos e comunicações internas para clientes.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Engajamento Direto: Permite enviar links e textos personalizados.
 * - Tipagem PHP 8.4: Uso de bool para status de leitura e int para IDs.
 * - Relacionamentos: #[ManyToOne] para vincular a notificação ao destinatário (Customer).
 */
class Notification extends BaseEntity
{
    private int $customerId = 0;
    private string $sender = '';
    private string $title = '';
    private string $text = '';
    private string $link = '';
    private bool $status = false;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $id): self { $this->customerId = $id; return $this; }

    public function getSender(): string { return $this->sender; }
    public function setSender(string $sender): self { $this->sender = $sender; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getText(): string { return $this->text; }
    public function setText(string $text): self { $this->text = $text; return $this; }

    public function getLink(): string { return $this->link; }
    public function setLink(string $link): self { $this->link = $link; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $status): self { $this->status = $status; return $this; }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }
}