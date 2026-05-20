<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CategoryFilter - Define quais filtros são aplicáveis a uma categoria.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Filtro Inteligente: Mapeia a relação n-n entre categorias e o módulo de filtros modernizado.
 */
class CategoryFilter extends BaseEntity
{
    private int $categoryId = 0;
    private int $filterId = 0;

    #[ManyToOne(targetEntity: Category::class, foreignKey: 'categoryId')]
    private ?Category $category = null;

    #[ManyToOne(targetEntity: Filter::class, foreignKey: 'filterId')]
    private ?Filter $filter = null;

    public function getCategoryId(): int { return $this->categoryId; }
    public function setCategoryId(int $value): self { $this->categoryId = $value; return $this; }

    public function getFilterId(): int { return $this->filterId; }
    public function setFilterId(int $value): self { $this->filterId = $value; return $this; }
}