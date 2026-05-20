<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

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

    #[ManyToOne(targetEntity: GeoZone::class, foreignKey: 'geoZoneId')]
    private ?GeoZone $geoZone = null;

    /**
     * Apontamentos Técnicos:
     * 1. Precisão de Cálculo: O campo 'rate' é float para suportar alíquotas complexas 
     *    (ex: 17.5%) sem perda de centavos no motor de checkout.
     * 2. Localização Fiscal: O vínculo com GeoZone garante que o imposto só seja 
     *    aplicado se o cliente pertencer à zona geográfica configurada.
     */

    public function getGeoZoneId(): int
    {
        return $this->geoZone ? (int)$this->geoZone->getId() : 0;
    }

    public function setGeoZoneId(int $geoZoneId): self
    {
        if (!$this->geoZone) $this->geoZone = new GeoZone();
        $this->geoZone->setId($geoZoneId);
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

    public function getGeoZone(): ?GeoZone
    {
        return $this->geoZone;
    }

    public function setGeoZone(?GeoZone $geoZone): self
    {
        $this->geoZone = $geoZone;
        return $this;
    }
}