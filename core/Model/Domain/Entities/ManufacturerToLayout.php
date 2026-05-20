<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ManufacturerToLayout - Define layouts customizados para marcas por loja.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Flexibilidade de Design: Permite páginas de marcas exclusivas por Store.
 * - Tipagem PHP 8.4: IDs tipados como int.
 * - Relacionamentos: #[ManyToOne] para Manufacturer, Store e Layout.
 */
class ManufacturerToLayout extends BaseEntity
{
    private int $manufacturerId = 0;
    private int $storeId = 0;
    private int $layoutId = 0;

    #[ManyToOne(targetEntity: Manufacturer::class, foreignKey: 'manufacturerId')]
    private ?Manufacturer $manufacturer = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Layout::class, foreignKey: 'layoutId')]
    private ?Layout $layout = null;

    public function getManufacturerId(): int { return $this->manufacturerId; }
    public function setManufacturerId(int $id): self { $this->manufacturerId = $id; return $this; }

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $id): self { $this->storeId = $id; return $this; }

    public function getLayoutId(): int { return $this->layoutId; }
    public function setLayoutId(int $id): self { $this->layoutId = $id; return $this; }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }
    public function setManufacturer(?Manufacturer $m): self { $this->manufacturer = $m; return $this; }

    public function getStore(): ?Store
    {
        return $this->store;
    }
    public function setStore(?Store $store): self { $this->store = $store; return $this; }

    public function getLayout(): ?Layout { return $this->layout; }
    public function setLayout(?Layout $layout): self { $this->layout = $layout; return $this; }
}