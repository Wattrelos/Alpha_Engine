<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade LengthClass - Representa uma unidade de medida de comprimento (ex: Centímetro, Polegada).
 *
 * Alpha Engine:
 * - Tipagem estrita PHP 8.4 para o valor de conversão.
 * - Relacionamento OneToMany para suas descrições multi-idioma.
 */
class LengthClass extends BaseEntity
{
    protected float $value = 0.00000000; // Valor de conversão para a unidade padrão (ex: 1 para cm)

    #[OneToMany(targetEntity: LengthClassDescription::class, foreignKey: 'lengthClassId')]
    protected array $descriptions = [];

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Retorna a descrição da classe de comprimento para um idioma específico.
     */
    public function getDescription(int $languageId): ?LengthClassDescription
    {
        foreach ($this->descriptions as $description) {
            if ($description->getLanguageId() === $languageId) {
                return $description;
            }
        }
        return null;
    }

    /**
     * Retorna todas as descrições.
     */
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $descriptions): self
    {
        $this->descriptions = $descriptions;
        return $this;
    }

    /**
     * Adiciona uma única descrição à classe de comprimento.
     */
    public function addDescription(LengthClassDescription $description): self
    {
        $this->descriptions[] = $description;
        return $this;
    }
}