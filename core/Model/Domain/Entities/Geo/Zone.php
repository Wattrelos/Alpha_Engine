<?php

namespace Alpha\Model\Domain\Entities\Geo;

use Alpha\Model\Domain\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Alpha\Model\Domain\Attributes\ManyToOne;
/*
#[ORM\Entity]
#[ORM\Table(name: "agsc_geo_zones")]
#[ORM\UniqueConstraint(name: "uk_country_zone_iso", columns: ["country_id", "iso_code"])]
*/

class Zone extends BaseEntity
{
    // #[ORM\ManyToOne(targetEntity: Country::class)]
    // #[ORM\JoinColumn(name: "country_id", referencedColumnName: "id", nullable: false, onDelete: "RESTRICT")]
    #[ManyToOne(targetEntity: Country::class, foreignKey: 'countryId')]
    private ?Country $country = null;

    // #[ORM\Column(type: "string", length: 10)]
    private string $isoCode; // Código ISO 3166-2 (Ex: 'BR-SP')

    // #[ORM\Column(type: "string", length: 100)]
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
