<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductFilter
 * Tabela de ligação entre produtos e filtros do catálogo.
 * 
 * @Table(name="product_filter")
 */
class ProductFilter extends BaseEntity
{
    private int $productId = 0;
    private int $filterId = 0;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Filter::class, foreignKey: 'filterId')]
    private ?Filter $filter = null;

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getFilterId(): int
    {
        return $this->filterId;
    }

    public function setFilterId(int $filterId): self
    {
        $this->filterId = $filterId;
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

    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

    public function setFilter(?Filter $filter): self
    {
        $this->filter = $filter;
        return $this;
    }
}
