<?php

namespace Alpha\Model\Domain\Entities\Geo\Supplier;

class Supplier extends BaseEntity
{
    private string $id;
    private string $uuid;
    private string $name;
    private string $email;
    private string $phone;
    private string $status;

    private Addresses $addresses;
    private string $city;
    private string $state;

    private string $country;

    public function __construct() {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
