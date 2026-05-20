<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CategoryPath - Gerencia a hierarquia da árvore de categorias.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Performance de Navegação: Permite reconstruir caminhos (breadcrumbs) sem recursividade excessiva.
 * - Tipagem PHP 8.4: Uso de tipos nativos para IDs e níveis.
 * - Relacionamentos: Atributos #[ManyToOne] para vincular a categoria ao seu ancestral (path).
 */
class CategoryPath extends BaseEntity
{
    private int $level = 0;

    #[ManyToOne(targetEntity: Category::class, foreignKey: 'categoryId')]
    private ?Category $category = null;

    #[ManyToOne(targetEntity: Category::class, foreignKey: 'pathId')]
    private ?Category $path = null;

    public function getCategoryId(): int 
    { 
        return $this->category ? (int)$this->category->getId() : 0; 
    }
    
    public function setCategoryId(int $id): self 
    { 
        if (!$this->category) {
            $this->category = new Category();
        }
        $this->category->setId($id); 
        return $this; 
    }

    public function getPathId(): int 
    { 
        return $this->path ? (int)$this->path->getId() : 0; 
    }
    
    public function setPathId(int $id): self 
    { 
        if (!$this->path) {
            $this->path = new Category();
        }
        $this->path->setId($id); 
        return $this; 
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;
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

    public function getPath(): ?Category
    {
        return $this->path;
    }

    public function setPath(?Category $path): self
    {
        $this->path = $path;
        return $this;
    }
}