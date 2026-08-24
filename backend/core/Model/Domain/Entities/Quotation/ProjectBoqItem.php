<?php

namespace Alpha\Model\Domain\Entities\Quotation;

use Alpha\Model\Domain\BaseEntity;

/**
 * ProjectBoqItem - Item individual da lista quantitativa de materiais (BoQ).
 * Atende aos requisitos funcionais RF036 e RF037.
 */
class ProjectBoqItem extends BaseEntity
{
    private int $boqId = 0;
    private ?int $productId = null;
    private string $itemName = '';
    private string $unit = 'un';
    private float $quantity = 1.0;
    private float $unitPrice = 0.0;
    private float $totalPrice = 0.0;
    private ?string $notes = null;
    private ?string $dateAdded = null;

    public function getBoqId(): int { return $this->boqId; }
    public function setBoqId(int $boqId): self { $this->boqId = $boqId; return $this; }

    public function getProductId(): ?int { return $this->productId; }
    public function setProductId(?int $productId): self { $this->productId = $productId; return $this; }

    public function getItemName(): string { return $this->itemName; }
    public function setItemName(string $itemName): self { $this->itemName = $itemName; return $this; }

    public function getUnit(): string { return $this->unit; }
    public function setUnit(string $unit): self { $this->unit = $unit; return $this; }

    public function getQuantity(): float { return $this->quantity; }
    public function setQuantity(float $quantity): self { $this->quantity = $quantity; return $this; }

    public function getUnitPrice(): float { return $this->unitPrice; }
    public function setUnitPrice(float $unitPrice): self { $this->unitPrice = $unitPrice; return $this; }

    public function getTotalPrice(): float { return $this->totalPrice; }
    public function setTotalPrice(float $totalPrice): self { $this->totalPrice = $totalPrice; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): self { $this->notes = $notes; return $this; }

    public function getDateAdded(): ?string { return $this->dateAdded; }
    public function setDateAdded(?string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }
}
