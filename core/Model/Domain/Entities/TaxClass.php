<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade TaxClass (Classe de Imposto)
 * Agrupa diferentes regras fiscais que podem ser aplicadas a um produto.
 * 
 * @Table(name="tax_class")
 */
class TaxClass extends BaseEntity
{
    private string $title = '';
    private string $description = '';

    #[OneToMany(targetEntity: TaxRule::class, foreignKey: 'taxClassId')]
    private array $rules = [];

    /**
     * Apontamentos Técnicos:
     * 1. Agrupamento de Regras: Um produto aponta para uma TaxClass, que por sua vez 
     *    contém múltiplas TaxRules (ex: ISS + ICMS).
     * 2. Hidratação Automática: O atributo OneToMany permite ao DAO carregar todas 
     *    as regras e suas respectivas alíquotas (TaxRate) em cascata.
     */

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /** @return TaxRule[] */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }
}
