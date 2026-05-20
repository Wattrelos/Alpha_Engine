<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CategoryToLayout - Vincula uma categoria a um layout específico por loja.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Flexibilidade Visual: Permite que a mesma categoria tenha designs diferentes dependendo da Store.
 * - Tipagem Estrita: Propriedades inteiras para garantir integridade nas chaves estrangeiras.
 * - Mapeamento Relacional: #[ManyToOne] para Category, Store e Layout.
 */
class CategoryToLayout extends BaseEntity
{
    private int $categoryId = 0;
    private int $storeId = 0;
    private int $layoutId = 0;

    #[ManyToOne(targetEntity: Category::class, foreignKey: 'categoryId')]
    private ?Category $category = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Layout::class, foreignKey: 'layoutId')]
    private ?Layout $layout = null;

    public function getCategoryId(): int { return $this->categoryId; }
    public function setCategoryId(int $id): self { $this->categoryId = $id; return $this; }

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $id): self { $this->storeId = $id; return $this; }

    public function getLayoutId(): int { return $this->layoutId; }
    public function setLayoutId(int $id): self { $this->layoutId = $id; return $this; }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
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