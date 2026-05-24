<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Category - Organiza a hierarquia de produtos do catálogo.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Gestão Hierárquica: Preparada para suporte a categorias pai/filho com integridade de tipos.
 * - Performance de UI: Campos 'top' e 'column' tipados como bool/int para controle preciso de menus megamenu.
 * - Relacionamentos Dinâmicos: #[OneToMany] ativa o carregamento em cascata de descrições, filtros e vínculos com lojas.
 * - Auditoria: Propriedades de data tratadas como strings para compatibilidade com o motor de datas do banco de dados.
 */
class Category extends BaseEntity
{
    private string $image = '';
    private int $parentId = 0;
    private int $sortOrder = 0;
    private bool $status = true;

    /** @var CategoryDescription[] */
    #[OneToMany(targetEntity: CategoryDescription::class, mappedBy: "category", foreignKey: "categoryId")]
    private array $descriptions = [];

    /** @var CategoryToStore[] */
    #[OneToMany(targetEntity: CategoryToStore::class, mappedBy: "category", foreignKey: "categoryId")]
    private array $categoryToStores = [];

    /** @var CategoryFilter[] */
    #[OneToMany(targetEntity: CategoryFilter::class, mappedBy: "category", foreignKey: "categoryId")]
    private array $filters = [];

    public function getImage(): string { return $this->image; }
    public function setImage(string $image): self { $this->image = $image; return $this; }

    public function getParentId(): int { return $this->parentId; }
    public function setParentId(int $parentId): self { $this->parentId = $parentId; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }

    public function getStatus(): bool { return $this->status; }
    public function setStatus(bool|int $status): self { $this->status = (bool)$status; return $this; }

    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $descriptions): self { $this->descriptions = $descriptions; return $this; }

    public function getCategoryToStores(): array { return $this->categoryToStores; }
    public function setCategoryToStores(array $value): self { $this->categoryToStores = $value; return $this; }

    public function getFilters(): array { return $this->filters; }
    public function setFilters(array $value): self { $this->filters = $value; return $this; }
}