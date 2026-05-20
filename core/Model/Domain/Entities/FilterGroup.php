<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade FilterGroup - Agrupador de filtros (ex: Cores, Tamanhos).
 */
class FilterGroup extends BaseEntity
{
    private int $sortOrder = 0;

    /**
     * @var Filter[]
     */
    #[OneToMany(targetEntity: Filter::class, mappedBy: "filterGroup", foreignKey: "filterGroupId")]
    private array $filters = [];

    /**
     * @var FilterGroupDescription[]
     */
    #[OneToMany(targetEntity: FilterGroupDescription::class, mappedBy: "filterGroup", foreignKey: "filterGroupId")]
    private array $descriptions = [];

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $value): self
    {
        $this->sortOrder = $value;
        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $value): self
    {
        $this->filters = $value;
        return $this;
    }

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $value): self
    {
        $this->descriptions = $value;
        return $this;
    }

}
