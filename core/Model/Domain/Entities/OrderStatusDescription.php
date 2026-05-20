<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade OrderStatusDescription - Traduções para os nomes de status do pedido.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Localização Nativa: Permite que o fluxo de venda se adapte ao idioma do cliente.
 * - Integridade Relacional: Vínculo ManyToOne com Language e OrderStatus pai.
 */
class OrderStatusDescription extends BaseEntity
{
    private int $orderStatusId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: OrderStatus::class, foreignKey: 'orderStatusId')]
    private ?OrderStatus $orderStatus = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getOrderStatusId(): int { return $this->orderStatusId; }
    public function setOrderStatusId(int $id): self { $this->orderStatusId = $id; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $id): self { $this->languageId = $id; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getOrderStatus(): ?OrderStatus { return $this->orderStatus; }
    public function setOrderStatus(?OrderStatus $status): self { $this->orderStatus = $status; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}