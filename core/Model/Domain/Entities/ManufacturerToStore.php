<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ManufacturerToStore - Define em quais lojas um fabricante está disponível.
 */
class ManufacturerToStore extends BaseEntity
{
    private int $manufacturerId = 0;
    private int $storeId = 0;

    #[ManyToOne(targetEntity: Manufacturer::class, foreignKey: 'manufacturerId')]
    private ?Manufacturer $manufacturer = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    public function getManufacturerId(): int
    {
        return $this->manufacturerId;
    }

    public function setManufacturerId(int $value): self
    {
        $this->manufacturerId = $value;
        return $this;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $value): self
    {
        $this->storeId = $value;
        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): self
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }
}