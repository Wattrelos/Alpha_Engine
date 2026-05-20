<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade OptionValueDescription - Tradução para os valores das opções.
 * Tradução do valor (importante para tamanhos: "G" em PT-BR, "L" em EN).
 */
class OptionValueDescription extends BaseEntity
{
    private int $optionValueId = 0;
    private int $languageId = 0;
    private int $optionId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: OptionValue::class, foreignKey: 'optionValueId')]
    private ?OptionValue $optionValue = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getOptionValueId(): int
    {
        return $this->optionValueId;
    }

    public function setOptionValueId(int $value): self
    {
        $this->optionValueId = $value;
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

    public function getOptionId(): int
    {
        return $this->optionId;
    }

    public function setOptionId(int $value): self
    {
        $this->optionId = $value;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getOptionValue(): ?OptionValue
    {
        return $this->optionValue;
    }

    public function setOptionValue(?OptionValue $optionValue): self
    {
        $this->optionValue = $optionValue;
        return $this;
    }
}