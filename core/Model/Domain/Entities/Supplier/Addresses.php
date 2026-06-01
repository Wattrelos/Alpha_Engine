<?php

namespace Alpha\Model\Domain\Entities\Geo\Supplier;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Entities\Geo\Country;
use Alpha\Model\Domain\Entities\Geo\Zone;
use Alpha\Model\Domain\Entities\Geo\City;

class Address extends BaseEntity
{
    public const TABLE_NAME = 'agsc_supplier_addresses';

    // id BIGINT PK (Surrogate Key Auto Increment) Herdada de BaseEntity
    private int $supplierId;     // BIGINT FK para a tabela principal de fornecedores

    // Associações de Domínio (Objetos em vez de IDs textuais ou numéricos puros)
    #[ManyToOne(targetEntity: Country::class, foreignKey: 'countryId')]
    private ?Country $country;    // Instância da classe Geo\Country

    #[ManyToOne(targetEntity: Zone::class, foreignKey: 'zoneId')]
    private ?Zone $zone;          // Instância da classe Geo\Zone

    #[ManyToOne(targetEntity: City::class, foreignKey: 'cityId')]
    private ?City $city;          // Instância da classe Geo\City

    private string $postalCode;  // CEP / ZIP / Postnummer
    private string $street;      // Rua, Avenida, etc.
    private string $number;      // Número do estabelecimento
    private ?string $complement; // Complemento (Opcional, aceita nulo)
    private ?string $district;   // Bairro (Opcional, aceita nulo para a Europa)

    public function __construct() {}

    // --- MÉTODOS GETTERS ---
    public function getId(): int
    {
        return $this->id;
    }
    public function getSupplierId(): int
    {
        return $this->supplierId;
    }
    public function getCountry(): Country
    {
        return $this->country;
    }
    public function getZone(): Zone
    {
        return $this->zone;
    }
    public function getCity(): City
    {
        return $this->city;
    }
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }
    public function getStreet(): string
    {
        return $this->street;
    }
    public function getNumber(): string
    {
        return $this->number;
    }
    public function getComplement(): ?string
    {
        return $this->complement;
    }
    public function getDistrict(): ?string
    {
        return $this->district;
    }
}
