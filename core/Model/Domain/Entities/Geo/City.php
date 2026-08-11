<?php

namespace Alpha\Model\Domain\Entities\Geo;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

// Como esta classe fica em uma subpasta, temos que utilizar alias para o namespace, por exemplo:
// use Alpha\Model\Domain\Entities\Geo\Zone as GeoCity;
// Isso ocorre porque estamos utilizando o namespace Alpha\Model\Domain\Entities\Geo na classe City
// E a classe Zone também está no mesmo namespace, então para evitar conflito, usamos o alias.

class City extends BaseEntity
{
    public const TABLE_NAME = 'agsc_geo_cities';

    private string $name;
    private bool $isServed;

    // Associação do tipo "Muitas Cidades pertencem a uma Zona" (Many-to-One)
    #[ManyToOne(targetEntity: Zone::class, foreignKey: 'zoneId')]
    private ?Zone $zone = null;


    public function __construct(int $id = 0, string $name = '', ?Zone $zone = null, bool $isServed = false)
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
    public function setZone(?Zone $zone): self
    {
        $this->zone = $zone;
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
    public function getIsServed(): bool
    {
        return $this->isServed;
    }
    public function setIsServed(bool $isServed): self
    {
        $this->isServed = $isServed;
        return $this;
    }
}
