<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;
 use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Filter - Representa um item de filtro individual no catálogo.
 */
class Filter extends BaseEntity
{
    private int $filterGroupId = 0;
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: FilterGroup::class, foreignKey: 'filterGroupId')]
    private ?FilterGroup $filterGroup = null;

    /**
     * @var FilterDescription[]
     */
    #[OneToMany(targetEntity: FilterDescription::class, mappedBy: "filter", foreignKey: "filterId")]
    private array $descriptions = [];

    public function getFilterGroupId(): int
    {
        return $this->filterGroupId;
    }

    public function setFilterGroupId(int $value): self
    {
        $this->filterGroupId = $value;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $value): self
    {
        $this->sortOrder = $value;
        return $this;
    }

    public function getFilterGroup(): ?FilterGroup
    {
        return $this->filterGroup;
    }

    public function setFilterGroup(?FilterGroup $value): self
    {
        $this->filterGroup = $value;
        return $this;
    }

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $descriptions): self
    {
        $this->descriptions = $descriptions;
        return $this;
    }
}
