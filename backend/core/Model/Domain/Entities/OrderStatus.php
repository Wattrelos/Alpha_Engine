<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade OrderStatus
 * Dicionário localizado dos estados de um pedido (Pendente, Processando, Completo).
 * 
 * @Table(name="order_status")
 */
class OrderStatus extends BaseEntity
{
    private int $orderStatusId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getOrderStatusId(): int
    {
        return $this->orderStatusId;
    }

    public function setOrderStatusId(int $orderStatusId): self
    {
        $this->orderStatusId = $orderStatusId;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;
        return $this;
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}