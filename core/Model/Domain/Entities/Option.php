<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Option - Define o tipo de variação de produto (ex: Cor, Tamanho).
 * Representa o cabeçalho da variação (ex: "Cor"). Ela define o tipo de campo (select, radio, checkbox, etc) e a ordem de exibição.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Recursividade de Dados: Mapeamento OneToMany para carregar automaticamente as descrições e a coleção de OptionValues.
 * - Tipagem Nativa: Garantia de integridade para campos de controle (type, sortOrder).
 */
class Option extends BaseEntity
{
    private string $type = '';
    private int $sortOrder = 0;
    private string $validation = '';

    /**
     * @var OptionDescription[]
     */
    #[OneToMany(targetEntity: OptionDescription::class, mappedBy: "option", foreignKey: "optionId")]
    private array $descriptions = [];

    /**
     * @var OptionValue[]
     */
    #[OneToMany(targetEntity: OptionValue::class, mappedBy: "option", foreignKey: "optionId")]
    private array $values = [];

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
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

    public function getValidation(): string
    {
        return $this->validation;
    }

    public function setValidation(string $validation): self
    {
        $this->validation = $validation;
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

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): self
    {
        $this->values = $values;
        return $this;
    }
}