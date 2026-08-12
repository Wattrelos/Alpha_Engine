<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade OrderOption
 * Registra as opções/variações selecionadas de um produto dentro de um pedido (ex: Cor: Azul, Tamanho: M).
 * 
 * @Table(name="order_option")
 */
class OrderOption extends BaseEntity
{
    private int $orderId = 0;
    private int $orderProductId = 0;
    private int $productOptionId = 0;
    private int $productOptionValueId = 0;
    private string $name = '';
    private string $value = '';
    private string $type = '';

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    #[ManyToOne(targetEntity: OrderProduct::class, foreignKey: 'orderProductId')]
    private ?OrderProduct $orderProduct = null;

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getOrderProductId(): int
    {
        return $this->orderProductId;
    }

    public function setOrderProductId(int $orderProductId): self
    {
        $this->orderProductId = $orderProductId;
        return $this;
    }

    public function getProductOptionId(): int
    {
        return $this->productOptionId;
    }

    public function setProductOptionId(int $productOptionId): self
    {
        $this->productOptionId = $productOptionId;
        return $this;
    }

    public function getProductOptionValueId(): int
    {
        return $this->productOptionValueId;
    }

    public function setProductOptionValueId(int $productOptionValueId): self
    {
        $this->productOptionValueId = $productOptionValueId;
        return $this;
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getValue(): string { return $this->value; }
    public function setValue(string $value): self { $this->value = $value; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { 
        $this->order = $order; return $this; 
    }

    public function getOrderProduct(): ?OrderProduct { return $this->orderProduct; }
    public function setOrderProduct(?OrderProduct $orderProduct): self { 
        $this->orderProduct = $orderProduct; return $this; 
    }
}