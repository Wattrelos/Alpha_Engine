<?php

namespace Alpha\Model\Domain\Entities\Supplier;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Entities\Geo\Country;
use Alpha\Model\Domain\Entities\Geo\Zone;
use Alpha\Model\Domain\Entities\Geo\City;

class Addresses extends BaseEntity
{
    public const TABLE_NAME = 'agsc_supplier_addresses';

    // id BIGINT PK (Surrogate Key Auto Increment) Herdada de BaseEntity
    private int $supplierId = 0;     // BIGINT FK para a tabela principal de fornecedores

    // Associações de Domínio (Objetos em vez de IDs textuais ou numéricos puros)
    #[ManyToOne(targetEntity: Country::class, foreignKey: 'countryId')]
    private ?Country $country = null;    // Instância da classe Geo\Country

    #[ManyToOne(targetEntity: Zone::class, foreignKey: 'zoneId')]
    private ?Zone $zone = null;          // Instância da classe Geo\Zone

    #[ManyToOne(targetEntity: City::class, foreignKey: 'cityId')]
    private ?City $city = null;          // Instância da classe Geo\City

    private string $postalCode = '';  // CEP / ZIP / Postnummer
    private string $street = '';      // Rua, Avenida, etc.
    private string $number = '';      // Número do estabelecimento
    private ?string $complement = null; // Complemento (Opcional, aceita nulo)
    private ?string $district = null;   // Bairro (Opcional, aceita nulo para a Europa)

    public function __construct() {}

    // --- MÉTODOS GETTERS E SETTERS ---
    public function getId(): int
    {
        return $this->id;
    }
    public function getSupplierId(): int
    {
        return $this->supplierId;
    }
    public function setSupplierId(int $supplierId): void
    {
        $this->supplierId = $supplierId;
    }
    public function getCountry(): ?Country
    {
        return $this->country;
    }
    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }
    public function getZone(): ?Zone
    {
        return $this->zone;
    }
    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }
    public function getCity(): ?City
    {
        return $this->city;
    }
    public function setCity(?City $city): void
    {
        $this->city = $city;
    }
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }
    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }
    public function getStreet(): string
    {
        return $this->street;
    }
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }
    public function getNumber(): string
    {
        return $this->number;
    }
    public function setNumber(string $number): void
    {
        $this->number = $number;
    }
    public function getComplement(): ?string
    {
        return $this->complement;
    }
    public function setComplement(?string $complement): void
    {
        $this->complement = $complement;
    }
    public function getDistrict(): ?string
    {
        return $this->district;
    }
    public function setDistrict(?string $district): void
    {
        $this->district = $district;
    }
}
