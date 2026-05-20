<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
/**
 * Entidade ProductViewed
 * Contador simples de visualizações por produto.
 * 
 * @Table(name="product_viewed")
 */
class ProductViewed extends BaseEntity
{
    private int $productId = 0;
    private int $viewed = 0;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getViewed(): int
    {
        return $this->viewed;
    }

    public function setViewed(int $viewed): self
    {
        $this->viewed = $viewed;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }
}
