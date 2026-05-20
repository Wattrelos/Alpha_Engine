<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Address - Representa o endereço do cliente.
 * Inclui extensões Alpha Engine para o mercado brasileiro.
 */
class Address extends BaseEntity
{
    private string $firstname = '';
    private string $lastname = '';
    private string $company = '';
    private string $address1 = '';
    private string $number = '';
    private string $address2 = '';
    private string $neighborhood = '';
    private string $city = '';
    private string $postcode = '';
    private bool $default = false;
    private string $customField = '';

    // Associações Muitos-para-Um

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: Country::class, foreignKey: 'countryId')]
    private ?Country $country = null;

    #[ManyToOne(targetEntity: Zone::class, foreignKey: 'zoneId')]
    private ?Zone $zone = null;

    public function getCustomerId(): int
    {
        return $this->customer ? (int)$this->customer->getId() : 0;
    }

    public function setCustomerId(int $value): self
    {
        if (!$this->customer) {
            $this->customer = new Customer();
        }
        $this->customer->setId($value);
        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $value): self
    {
        $this->firstname = $value;
        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $value): self
    {
        $this->lastname = $value;
        return $this;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $value): self
    {
        $this->company = $value;
        return $this;
    }

    public function getAddress1(): string
    {
        return $this->address1;
    }

    public function setAddress1(string $value): self
    {
        $this->address1 = $value;
        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $value): self
    {
        $this->number = $value;
        return $this;
    }

    public function getAddress2(): string
    {
        return $this->address2;
    }

    public function setAddress2(string $value): self
    {
        $this->address2 = $value;
        return $this;
    }

    public function getNeighborhood(): string
    {
        return $this->neighborhood;
    }

    public function setNeighborhood(string $value): self
    {
        $this->neighborhood = $value;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $value): self
    {
        $this->city = $value;
        return $this;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }

    public function setPostcode(string $value): self
    {
        $this->postcode = $value;
        return $this;
    }

    public function getCountryId(): int
    {
        return $this->country ? (int)$this->country->getId() : 0;
    }

    public function setCountryId(int $value): self
    {
        if (!$this->country) {
            $this->country = new Country();
        }
        $this->country->setId($value);
        return $this;
    }

    public function getZoneId(): int
    {
        return $this->zone ? (int)$this->zone->getId() : 0;
    }

    public function setZoneId(int $value): self
    {
        if (!$this->zone) {
            $this->zone = new Zone();
        }
        $this->zone->setId($value);
        return $this;
    }

    public function getDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $value): self
    {
        $this->default = (bool)$value;
        return $this;
    }

    public function getCustomField(): string
    {
        return $this->customField;
    }

    public function setCustomField(string $value): self
    {
        $this->customField = $value;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): self
    {
        $this->zone = $zone;
        return $this;
    }
}
