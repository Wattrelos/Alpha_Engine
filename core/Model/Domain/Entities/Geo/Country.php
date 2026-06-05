<?php

namespace Alpha\Model\Domain\Entities\Geo;

use Alpha\Model\Domain\BaseEntity;

// Novas classes de engereçamento e localização que susbstituirá as antigas classes e tabelasdo código legado:
// Usaremos o doctrine para criar as entidades com persistencia de dados no banco de dados. 

// Como esta classe fica em uma subpasta, temos que utilizar alias para o namespace, por exemplo:
// use Alpha\Model\Domain\Entities\Geo\Country as GeoCountry;
// Isso ocorre porque estamos utilizando o namespace Alpha\Model\Domain\Entities\Geo na classe Country
// E a classe Country também está no mesmo namespace, então para evitar conflito, usamos o alias.

class Country extends BaseEntity
{
    public const TABLE_NAME = 'agsc_geo_countries';

    // #[ORM\Column(type: "string", length: 100)]
    private string $name;

    // #[ORM\Column(type: "string", length: 2, unique: true)]
    private string $isoAlpha2;

    // #[ORM\Column(type: "string", length: 3, unique: true)]
    private string $isoAlpha3;

    // #[ORM\Column(type: "boolean")]
    private bool $isActive = true;


    // Getters e Settores...
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    public function getIsoAlpha2(): string
    {
        return $this->isoAlpha2;
    }
    public function setIsoAlpha2(string $isoAlpha2): self
    {
        $this->isoAlpha2 = $isoAlpha2;
        return $this;
    }
    public function getIsoAlpha3(): string
    {
        return $this->isoAlpha3;
    }
    public function setIsoAlpha3(string $isoAlpha3): self
    {
        $this->isoAlpha3 = $isoAlpha3;
        return $this;
    }
    public function getIsActive(): bool
    {
        return $this->isActive;
    }
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }
}
