<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade OrderTotal - Decomposição dos valores que compõem o total do pedido.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Transparência Financeira: Armazena sub-totais, fretes, cupons e impostos de forma isolada para auditoria.
 * - Precisão Monetária: Campo 'value' tipado como float para evitar divergências em somatórios de checkout.
 * - Injeção Relacional: Atributo #[ManyToOne] para vinculação direta com o Pedido pai.
 * - Padronização: 'code' identifica o tipo de total (sub_total, shipping, total, tax).
 */
class OrderTotal extends BaseEntity
{
    private string $code = '';
    private string $title = '';
    private float $value = 0.0000;
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    public function getOrderId(): int { return $this->order ? (int)$this->order->getId() : 0; }
    public function setOrderId(int $id): self { 
        if (!$this->order) {
            $this->order = new Order();
        }
        $this->order->setId($id); return $this; 
    }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getValue(): float { return $this->value; }
    public function setValue(float $value): self { $this->value = $value; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $order): self { $this->sortOrder = $order; return $this; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { $this->order = $order; return $this; }
}