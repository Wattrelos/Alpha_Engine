<?php

namespace Alpha\Model\Domain\Entities\Geo;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class City extends BaseEntity
{
    public const TABLE_NAME = 'agsc_geo_cities';

    private string $name;
    private bool $isServed;

    // Associação do tipo "Muitas Cidades pertencem a uma Zona" (Many-to-One)
    #[ManyToOne(targetEntity: Zone::class, foreignKey: 'zoneId')]
    private ?Zone $zone = null;


    public function __construct(int $id, string $name, Zone $zone, bool $isServed = false)
    {
        parent::__construct();
        $this->id = $id;
        $this->name = $name;
        $this->zone = $zone;
        $this->isServed = $isServed;
    }

    // Getters e Setters...
    public function getZone(): ?Zone
    {
        return $this->zone;
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
    public function isServed(): bool
    {
        return $this->isServed;
    }
    public function setIsServed(bool $isServed): self
    {
        $this->isServed = $isServed;
        return $this;
    }
}
