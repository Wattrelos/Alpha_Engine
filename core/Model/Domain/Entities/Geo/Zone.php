<?php

namespace Alpha\Model\Domain\Entities\Geo;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

// Como esta classe fica em uma subpasta, temos que utilizar alias para o namespace, por exemplo:
// use Alpha\Model\Domain\Entities\Geo\Zone as GeoZone;
// Isso ocorre porque estamos utilizando o namespace Alpha\Model\Domain\Entities\Geo na classe Zone
// E a classe Country também está no mesmo namespace, então para evitar conflito, usamos o alias.

class Zone extends BaseEntity
{
    public const TABLE_NAME = 'agsc_geo_zones';

    #[ManyToOne(targetEntity: Country::class, foreignKey: 'countryId')]
    private ?Country $country = null;

    private string $isoCode; // Código ISO 3166-2 (Ex: 'BR-SP')
    private string $name;

    // Getters e Settores...
    public function getCountry(): Country
    {
        return $this->country;
    }
    public function setCountry(Country $country): self
    {
        $this->country = $country;
        return $this;
    }
    public function getIsoCode(): string
    {
        return $this->isoCode;
    }
    public function setIsoCode(string $isoCode): self
    {
        $this->isoCode = $isoCode;
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
}
