<?php

namespace Alpha\Model\Domain\Entities\Supplier;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToMany;
use DateTimeInterface;
use Alpha\Model\Domain\Entities\Manufacturer;

class Contact extends BaseEntity
{
    public const TABLE_NAME = 'agsc_contact';

    private ?string $name = null;
    private ?string $email = null;
    private ?string $phone = null;
    private ?string $position = null;
    private ?bool $isActive = null;
    private ?DateTimeInterface $createdAt;
    private ?DateTimeInterface $updatedAt;

    #[ManyToMany(targetEntity: Manufacturer::class, foreignKey: 'contact_id', pivotTable: 'agsc_supplier_contact_brands', pivotOwn: 'contact_id', pivotTarget: 'brand_id')]
    private $brands;

    public function __construct() {}

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getBrands(): ?iterable
    {
        return $this->brands;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
