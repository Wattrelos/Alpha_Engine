<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade FilterDescription - Traduções para os itens de filtro.
 */
class FilterDescription extends BaseEntity
{
    private int $filterId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    #[ManyToOne(targetEntity: Filter::class, foreignKey: 'filterId')]
    private ?Filter $filter = null;

    public function getFilterId(): int
    {
        return $this->filterId;
    }

    public function setFilterId(int $value): self
    {
        $this->filterId = $value;
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

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $value): self
    {
        $this->language = $value;
        return $this;
    }

    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

    public function setFilter(?Filter $value): self
    {
        $this->filter = $value;
        return $this;
    }
}
