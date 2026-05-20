<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductToCategory
 * Tabela pivot que vincula produtos às suas categorias.
 * 
 * @Table(name="product_to_category")
 */
class ProductToCategory extends BaseEntity
{
    private int $productId = 0;
    private int $categoryId = 0;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Category::class, foreignKey: 'categoryId')]
    private ?Category $category = null;

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): self
    {
        $this->categoryId = $categoryId;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }
}
