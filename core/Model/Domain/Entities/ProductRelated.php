<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class ProductRelated extends BaseEntity
{
    #[ManyToOne(targetEntity: Product::class)]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Product::class)]
    private ?Product $related = null;

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getRelated(): ?Product
    {
        return $this->related;
    }

    public function setRelated(?Product $related): self
    {
        $this->related = $related;
        return $this;
    }
}