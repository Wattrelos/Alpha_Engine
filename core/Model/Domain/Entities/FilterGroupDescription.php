<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade FilterGroupDescription - Traduções para o nome do grupo de filtros.
 */
class FilterGroupDescription extends BaseEntity
{
    private int $filterGroupId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: FilterGroup::class, foreignKey: 'filterGroupId')]
    private ?FilterGroup $filterGroup = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getFilterGroupId(): int
    {
        return $this->filterGroupId;
    }

    public function setFilterGroupId(int $value): self
    {
        $this->filterGroupId = $value;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $value): self
    {
        $this->languageId = $value;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $value): self
    {
        $this->name = $value;
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

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $value): self
    {
        $this->language = $value;
        return $this;
    }
}
