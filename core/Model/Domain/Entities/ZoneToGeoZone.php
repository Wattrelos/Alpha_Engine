<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ZoneToGeoZone - Mapeamento individual de estados/países para uma GeoZone.
 */
class ZoneToGeoZone extends BaseEntity
{
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Country::class, foreignKey: 'countryId')]
    private ?Country $country = null;

    #[ManyToOne(targetEntity: Zone::class, foreignKey: 'zoneId')]
    private ?Zone $zone = null;

    #[ManyToOne(targetEntity: GeoZone::class, foreignKey: 'geoZoneId')]
    private ?GeoZone $geoZone = null;

    public function getCountryId(): int 
    { 
        return $this->country ? (int)$this->country->getId() : 0; 
    }
    
    public function setCountryId(int $id): self 
    { 
        if (!$this->country) $this->country = new Country();
        $this->country->setId($id); return $this; 
    }

    public function getZoneId(): int 
    { 
        return $this->zone ? (int)$this->zone->getId() : 0; 
    }
    
    public function setZoneId(int $id): self 
    { 
        if (!$this->zone) $this->zone = new Zone();
        $this->zone->setId($id); return $this; 
    }

    public function getGeoZoneId(): int { return $this->geoZone ? (int)$this->geoZone->getId() : 0; }
    public function setGeoZoneId(int $id): self 
    { 
        if (!$this->geoZone) $this->geoZone = new GeoZone();
        $this->geoZone->setId($id); 
        return $this; 
    }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    public function getCountry(): ?Country { return $this->country; }
    public function setCountry(?Country $country): self { $this->country = $country; return $this; }

    public function getZone(): ?Zone { return $this->zone; }
    public function setZone(?Zone $zone): self { $this->zone = $zone; return $this; }

    public function getGeoZone(): ?GeoZone { return $this->geoZone; }
    public function setGeoZone(?GeoZone $geoZone): self { $this->geoZone = $geoZone; return $this; }
}