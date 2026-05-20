<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade OptionDescription - Traduções para o nome da opção.
 */
class OptionDescription extends BaseEntity
{
    private int $optionId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Option::class, foreignKey: 'optionId')]
    private ?Option $option = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getOptionId(): int
    {
        return $this->optionId;
    }

    public function setOptionId(int $value): self
    {
        $this->optionId = $value;
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

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getOption(): ?Option
    {
        return $this->option;
    }

    public function setOption(?Option $option): self
    {
        $this->option = $option;
        return $this;
    }
}