<?php

namespace Alpha\Model\Domain\Entities\Supplier;

class Supplier extends BaseEntity
{
    private string $uuid;
    private string $name;
    private string $email;
    private string $phone;
    private string $status;

    // Alpha Engine: Associação estruturada com a entidade dependente
    private ?Addresses $addresses = null;

    public function __construct() {}

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

    public function getAddresses(): ?Addresses
    {
        return $this->addresses;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
