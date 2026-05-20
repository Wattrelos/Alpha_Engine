<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductRelated
 * Gerencia o relacionamento de "Produtos Relacionados".
 * 
 * @Table(name="product_related")
 */
class ProductRelated extends BaseEntity
{
    private int $productId = 0;
    private int $relatedId = 0;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'relatedId')]
    private ?Product $related = null;

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getRelatedId(): int
    {
        return $this->relatedId;
    }

    public function setRelatedId(int $relatedId): self
    {
        $this->relatedId = $relatedId;
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
