<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Entities\Zone;

/**
 * Entidade TaxRate (Alíquota de Imposto)
 * Define o valor real e o tipo de cálculo (Percentual ou Fixo).
 * 
 * @Table(name="tax_rate")
 */
class TaxRate extends BaseEntity
{
    private string $name = '';
    private float $rate = 0.0000;
    private string $type = 'P'; // 'P' para Percentual, 'F' para Fixo

    #[ManyToOne(targetEntity: Zone::class, foreignKey: 'zonesId')]
    private ?Zone $zone = null;

    /**
     * Apontamentos Técnicos:
     * 1. Precisão de Cálculo: O campo 'rate' é float para suportar alíquotas complexas 
     *    (ex: 17.5%) sem perda de centavos no motor de checkout.
     * 2. Localização Fiscal: O vínculo com GeoZone garante que o imposto só seja 
     *    aplicado se o cliente pertencer à zona geográfica configurada.
     */

    public function getZoneId(): int
    {
        return $this->zone ? (int)$this->zone->getId() : 0;
    }

    public function setZoneId(int $zoneId): self
    {
        if (!$this->zone) $this->zone = new Zone();
        $this->zone->setId($zoneId);
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

    public function getRate(): float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): self
    {
        $this->zone = $zone;
        return $this;
    }
}
