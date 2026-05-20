<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade OptionValue - Representa um valor individual de uma opção (ex: Vermelho).
 * Aqui residem os valores propriamente ditos (ex: "Vermelho", "Azul"). Ela pode conter uma imagem (comum em seletores de cores/swatches).
 */
class OptionValue extends BaseEntity
{
    private int $optionId = 0;
    private string $image = '';
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: Option::class, foreignKey: 'optionId')]
    private ?Option $option = null;

    /**
     * @var OptionValueDescription[]
     */
    #[OneToMany(targetEntity: OptionValueDescription::class, mappedBy: "optionValue", foreignKey: "optionValueId")]
    private array $descriptions = [];

    public function getOptionId(): int
    {
        return $this->optionId;
    }

    public function setOptionId(int $value): self
    {
        $this->optionId = $value;
        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
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