<?php

namespace Alpha\Model\Domain\Entities\Quotation;

use Alpha\Model\Domain\BaseEntity;

/**
 * ProjectBid - Proposta comercial submetida por um prestador de serviço para um RFQ.
 * Atende ao requisito funcional RF035 (Bid Comparison).
 */
class ProjectBid extends BaseEntity
{
    private int $rfqId = 0;
    private int $providerId = 0;
    private float $laborPrice = 0.0;
    private int $estimatedDurationDays = 1;
    private ?string $proposalNotes = null;
    private string $status = 'submitted'; // submitted, accepted, rejected, withdrawn
    private ?string $dateAdded = null;
    private ?string $dateModified = null;

    public function getRfqId(): int { return $this->rfqId; }
    public function setRfqId(int $rfqId): self { $this->rfqId = $rfqId; return $this; }

    public function getProviderId(): int { return $this->providerId; }
    public function setProviderId(int $providerId): self { $this->providerId = $providerId; return $this; }

    public function getLaborPrice(): float { return $this->laborPrice; }
    public function setLaborPrice(float $laborPrice): self { $this->laborPrice = $laborPrice; return $this; }

    public function getEstimatedDurationDays(): int { return $this->estimatedDurationDays; }
    public function setEstimatedDurationDays(int $estimatedDurationDays): self { $this->estimatedDurationDays = $estimatedDurationDays; return $this; }

    public function getProposalNotes(): ?string { return $this->proposalNotes; }
    public function setProposalNotes(?string $proposalNotes): self { $this->proposalNotes = $proposalNotes; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getDateAdded(): string { return $this->dateAdded ?: date('Y-m-d H:i:s'); }
    public function setDateAdded(?string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }

    public function getDateModified(): string { return $this->dateModified ?: date('Y-m-d H:i:s'); }
    public function setDateModified(?string $dateModified): self { $this->dateModified = $dateModified; return $this; }
}
