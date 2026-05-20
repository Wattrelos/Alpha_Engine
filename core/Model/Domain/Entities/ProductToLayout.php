<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductToLayout
 * Define layouts customizados por produto e por loja.
 * 
 * @Table(name="product_to_layout")
 */
class ProductToLayout extends BaseEntity
{
    private int $productId = 0;
    private int $storeId = 0;
    private int $layoutId = 0;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Layout::class, foreignKey: 'layoutId')]
    private ?Layout $layout = null;

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $storeId): self
    {
        $this->storeId = $storeId;
        return $this;
    }

    public function getLayoutId(): int
    {
        return $this->layoutId;
    }

    public function setLayoutId(int $layoutId): self
    {
        $this->layoutId = $layoutId;
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

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
}
