<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Manufacturer - Representa os fabricantes ou marcas no catálogo.
 */
class Manufacturer extends BaseEntity
{
    private string $name = '';
    private string $image = '';
    private int $sortOrder = 0;

    /**
     * @var ManufacturerToStore[]
     */
    #[OneToMany(targetEntity: ManufacturerToStore::class, mappedBy: "manufacturer", foreignKey: "manufacturerId")]
    private array $manufacturerToStores = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getManufacturerToStores(): array
    {
        return $this->manufacturerToStores;
    }

    public function setManufacturerToStores(array $value): self
    {
        $this->manufacturerToStores = $value;
        return $this;
    }
}