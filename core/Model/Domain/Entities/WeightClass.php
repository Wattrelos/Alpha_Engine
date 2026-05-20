<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade WeightClass (Classe de Peso)
 * Define o fator de conversão para unidades de medida de massa.
 * 
 * @Table(name="weight_class")
 */
class WeightClass extends BaseEntity
{
    private float $value = 1.00000000;

    #[OneToMany(targetEntity: WeightClassDescription::class, foreignKey: 'weightClassId')]
    private array $descriptions = [];

    /**
     * Apontamentos Técnicos:
     * 1. Precisão Decimal: O uso de float para 'value' é crítico para garantir que a conversão 
     *    entre gramas, quilos e libras seja matematicamente precisa no motor de frete.
     * 2. Hidratação de Descrições: O atributo OneToMany permite que o DAO carregue automaticamente
     *    os nomes e símbolos (kg, g) em todos os idiomas disponíveis.
     */

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
     * @return WeightClassDescription[]
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

    public function addDescription(WeightClassDescription $description): self
    {
        $this->descriptions[] = $description;
        return $this;
    }
}