<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CategoryToStore - Relaciona categorias com lojas específicas.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Abstração Relacional: Permite o isolamento de taxonomias em ambientes multi-loja.
 * - Carregamento Recursivo: DAO utiliza o atributo #[ManyToOne] para resolver chaves estrangeiras de forma automática.
 * - Tipagem PHP 8.4: Propriedades de ID tipadas para garantir integridade referencial e evitar erros de coerção de tipos.
 */
class CategoryToStore extends BaseEntity
{
    #[ManyToOne(targetEntity: Category::class, foreignKey: 'categoryId')]
    private ?Category $category = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    public function getCategoryId(): int 
    { 
        return $this->category ? (int)$this->category->getId() : 0; 
    }
    
    public function setCategoryId(int $value): self 
    { 
        if (!$this->category) {
            $this->category = new Category();
        }
        $this->category->setId($value); 
        return $this; 
    }

    public function getStoreId(): int 
    { 
        return $this->store ? (int)$this->store->getId() : 0; 
    }
    
    public function setStoreId(int $value): self 
    { 
        if (!$this->store) {
            $this->store = new Store();
        }
        $this->store->setId($value); 
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

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }
}
