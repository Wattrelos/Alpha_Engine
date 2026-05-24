<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Address
 * Representa o catálogo de endereços físicos (Livro de Endereços) de um cliente.
 * 
 * @Table(name="address")
 */
class Address extends BaseEntity
{
    private int $customerId = 0;
    private string $firstname = '';
    private string $lastname = '';
    private string $company = '';
    private string $address1 = '';
    private int $number = 0;
    private string $address2 = '';
    private string $neighborhood = '';
    private string $city = '';
    private string $postcode = '';
    private int $countryId = 0;
    private int $zoneId = 0;
    private string $customField = '';
    private bool $default = false;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: Country::class, foreignKey: 'countryId')]
    private ?Country $country = null;

    #[ManyToOne(targetEntity: Zone::class, foreignKey: 'zoneId')]
    private ?Zone $zone = null;

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $val): self { $this->customerId = $val; return $this; }

    public function getFirstname(): string { return $this->firstname; }
    public function setFirstname(string $val): self { $this->firstname = $val; return $this; }

    public function getLastname(): string { return $this->lastname; }
    public function setLastname(string $val): self { $this->lastname = $val; return $this; }

    public function getCompany(): string { return $this->company; }
    public function setCompany(string $val): self { $this->company = $val; return $this; }

    public function getAddress1(): string { return $this->address1; }
    public function setAddress1(string $val): self { $this->address1 = $val; return $this; }

    public function getNumber(): int { return $this->number; }
    public function setNumber(int $val): self { $this->number = $val; return $this; }

    public function getAddress2(): string { return $this->address2; }
    public function setAddress2(string $val): self { $this->address2 = $val; return $this; }

    public function getNeighborhood(): string { return $this->neighborhood; }
    public function setNeighborhood(string $val): self { $this->neighborhood = $val; return $this; }

    public function getCity(): string { return $this->city; }
    public function setCity(string $val): self { $this->city = $val; return $this; }

    public function getPostcode(): string { return $this->postcode; }
    public function setPostcode(string $val): self { $this->postcode = $val; return $this; }

    public function getCountryId(): int { return $this->countryId; }
    public function setCountryId(int $val): self { $this->countryId = $val; return $this; }

    public function getZoneId(): int { return $this->zoneId; }
    public function setZoneId(int $val): self { $this->zoneId = $val; return $this; }

    public function getCustomField(): string { return $this->customField; }
    public function setCustomField(string $val): self { $this->customField = $val; return $this; }

    public function isDefault(): bool { return $this->default; }
    public function setDefault(bool $val): self { $this->default = $val; return $this; }

    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $val): self { $this->customer = $val; return $this; }

    public function getCountry(): ?Country { return $this->country; }
    public function setCountry(?Country $val): self { $this->country = $val; return $this; }

    public function getZone(): ?Zone { return $this->zone; }
    public function setZone(?Zone $val): self { $this->zone = $val; return $this; }
}