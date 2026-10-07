<?php

namespace Alpha\Model\Domain\Entities\Quotation;

use Alpha\Model\Domain\BaseEntity;

/**
 * ProjectBoq - Lista técnica quantitativa de materiais (Bill of Quantities / MTO).
 * Atende aos requisitos funcionais RF036 (MTO / BoQ) e RF037 (Conversão em Cotação / Carrinho).
 */
class ProjectBoq extends BaseEntity
{
    private int $rfqId = 0;
    private int $providerId = 0;
    private string $title = 'Levantamento de Materiais (BoQ)';
    private ?string $notes = null;
    private float $totalEstimatedAmount = 0.0;
    private string $status = 'draft'; // draft, submitted, approved, converted_to_cart
    private ?string $dateAdded = null;
    private ?string $dateModified = null;

    /** @var ProjectBoqItem[] */
    private array $items = [];

    public function getRfqId(): int { return $this->rfqId; }
    public function setRfqId(int $rfqId): self { $this->rfqId = $rfqId; return $this; }

    public function getProviderId(): int { return $this->providerId; }
    public function setProviderId(int $providerId): self { $this->providerId = $providerId; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): self { $this->notes = $notes; return $this; }

    public function getTotalEstimatedAmount(): float { return $this->totalEstimatedAmount; }
    public function setTotalEstimatedAmount(float $totalEstimatedAmount): self { $this->totalEstimatedAmount = $totalEstimatedAmount; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getDateAdded(): string { return $this->dateAdded ?: date('Y-m-d H:i:s'); }
    public function setDateAdded(?string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }

    public function getDateModified(): string { return $this->dateModified ?: date('Y-m-d H:i:s'); }
    public function setDateModified(?string $dateModified): self { $this->dateModified = $dateModified; return $this; }

    /**
     * @return ProjectBoqItem[]
     */
    public function getItems(): array { return $this->items; }

    /**
     * @param ProjectBoqItem[] $items
     */
    public function setItems(array $items): self { $this->items = $items; return $this; }

    public function addItem(ProjectBoqItem $item): self
    {
        $this->items[] = $item;
        return $this;
    }
}
