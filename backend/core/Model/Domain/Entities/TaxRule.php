<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade TaxRule (Regra de Imposto)
 * Associa uma Classe de Imposto a uma Alíquota com base no contexto (Endereço, Prioridade).
 * 
 * @Table(name="tax_rule")
 */
class TaxRule extends BaseEntity
{
    private int $taxClassId = 0;
    private int $taxRateId = 0;
    private string $based = 'shipping'; // 'shipping', 'payment' ou 'store'
    private int $priority = 0;

    #[ManyToOne(targetEntity: TaxClass::class, foreignKey: 'taxClassId')]
    private ?TaxClass $taxClass = null;

    #[ManyToOne(targetEntity: TaxRate::class, foreignKey: 'taxRateId')]
    private ?TaxRate $taxRate = null;

    /**
     * Apontamentos Técnicos:
     * 1. Origem do Cálculo (Based): Define se o imposto deve ser calculado com base 
     *    no endereço de entrega, cobrança ou endereço fixo da loja.
     * 2. Prioridade: O motor de taxas executa as regras em ordem; prioridades iguais 
     *    podem ser somadas, enquanto diferentes são aplicadas sequencialmente.
     */

    public function getTaxClassId(): int
    {
        return $this->taxClassId;
    }

    public function setTaxClassId(int $taxClassId): self
    {
        $this->taxClassId = $taxClassId;
        return $this;
    }

    public function getTaxRateId(): int
    {
        return $this->taxRateId;
    }

    public function setTaxRateId(int $taxRateId): self
    {
        $this->taxRateId = $taxRateId;
        return $this;
    }

    public function getBased(): string
    {
        return $this->based;
    }

    public function setBased(string $based): self
    {
        $this->based = $based;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getTaxRate(): ?TaxRate
    {
        return $this->taxRate;
    }

    public function setTaxRate(?TaxRate $taxRate): self
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    public function getTaxClass(): ?TaxClass
    {
        return $this->taxClass;
    }

    public function setTaxClass(?TaxClass $taxClass): self
    {
        $this->taxClass = $taxClass;
        return $this;
    }
}
