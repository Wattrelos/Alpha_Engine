<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class ProductReturn extends BaseEntity
{
    private int $productReturnId = 0;
    private int $orderReturnId = 0;
    private int $orderProductId = 0;
    private int $quantity = 0;

    #[ManyToOne(targetEntity: OrderReturn::class, foreignKey: 'orderReturnId')]
    private ?OrderReturn $orderReturn = null;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'orderProductId')]
    private ?Product $product = null;

    public function getProductReturnId(): int
    {
        return $this->productReturnId;
    }
    public function setProductReturnId(int $val): self
    {
        $this->productReturnId = $val;
        return $this;
    }

    public function getOrderReturnId(): int
    {
        return $this->orderReturnId;
    }
    public function setOrderReturnId(int $val): self
    {
        $this->orderReturnId = $val;
        return $this;
    }

    public function getOrderProductId(): int
    {
        return $this->orderProductId;
    }
    public function setOrderProductId(int $val): self
    {
        $this->orderProductId = $val;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
    public function setQuantity(int $val): self
    {
        $this->quantity = $val;
        return $this;
    }

    public function getOrderReturn(): ?OrderReturn
    {
        return $this->orderReturn;
    }
    public function setOrderReturn(?OrderReturn $val): self
    {
        $this->orderReturn = $val;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }
    public function setProduct(?Product $val): self
    {
        $this->product = $val;
        return $this;
    }
}
