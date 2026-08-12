<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * DEPRECATED. Entidade GeoZone - Agrupamento geográfico para fins tributários e logísticos.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Agrupamento Fiscal: Permite a definição de regiões para aplicação de regras de impostos.
 * - Relacionamentos: OneToMany para ZoneToGeoZone para navegação completa da malha geográfica.
 * - Tipagem Estrita: Datas e nomes tratados como strings limpas.
 */
class GeoZone extends BaseEntity
{
    private string $name = '';
    private string $description = '';

    #[OneToMany(targetEntity: ZoneToGeoZone::class, foreignKey: 'geoZoneId')]
    private array $zones = [];

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): self
    {
        $this->name = $name;
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

    /** @return ZoneToGeoZone[] */
    public function getZones(): array
    {
        return $this->zones;
    }
    public function setZones(array $zones): self
    {
        $this->zones = $zones;
        return $this;
    }
}
