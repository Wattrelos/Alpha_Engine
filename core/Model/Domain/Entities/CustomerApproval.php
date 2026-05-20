<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use DateTimeImmutable;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerApproval
 * Gerencia as solicitações de aprovação pendentes para clientes ou afiliados.
 * 
 * @Table(name="customer_approval")
 */
class CustomerApproval extends BaseEntity
{
    private int $customerId = 0;
    private string $type = 'customer';
    private ?DateTimeImmutable $dateAdded = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    /**
     * Apontamentos Técnicos:
     * 1. Fluxo de Auditoria: Mantém o registro de quem ainda precisa ser aprovado para acessar a loja.
     * 2. Tipagem: O campo 'type' diferencia se a aprovação é para conta de cliente padrão ou afiliado.
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
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