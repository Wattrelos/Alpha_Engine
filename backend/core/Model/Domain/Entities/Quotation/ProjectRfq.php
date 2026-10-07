<?php

namespace Alpha\Model\Domain\Entities\Quotation;

use Alpha\Model\Domain\BaseEntity;

/**
 * ProjectRfq - Solicitação formal de orçamento de projeto emitida pelo cliente.
 * Atende ao requisito funcional RF033 (RFQ).
 */
class ProjectRfq extends BaseEntity
{
    private int $customerId = 0;
    private string $title = '';
    private string $category = 'geral';
    private string $description = '';
    private string $addressCep = '';
    private ?string $addressStreet = null;
    private ?string $addressNumber = null;
    private ?string $addressNeighborhood = null;
    private string $addressCity = '';
    private string $addressState = '';
    private ?float $latitude = null;
    private ?float $longitude = null;
    private float $budgetExpectation = 0.0;
    private int $desiredDeadlineDays = 30;
    private string $status = 'open'; // open, bidding, in_progress, completed, cancelled
    private ?int $selectedProviderId = null;
    private ?string $dateAdded = null;
    private ?string $dateModified = null;

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $customerId): self { $this->customerId = $customerId; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getAddressCep(): string { return $this->addressCep; }
    public function setAddressCep(string $addressCep): self { $this->addressCep = $addressCep; return $this; }

    public function getAddressStreet(): ?string { return $this->addressStreet; }
    public function setAddressStreet(?string $addressStreet): self { $this->addressStreet = $addressStreet; return $this; }

    public function getAddressNumber(): ?string { return $this->addressNumber; }
    public function setAddressNumber(?string $addressNumber): self { $this->addressNumber = $addressNumber; return $this; }

    public function getAddressNeighborhood(): ?string { return $this->addressNeighborhood; }
    public function setAddressNeighborhood(?string $addressNeighborhood): self { $this->addressNeighborhood = $addressNeighborhood; return $this; }

    public function getAddressCity(): string { return $this->addressCity; }
    public function setAddressCity(string $addressCity): self { $this->addressCity = $addressCity; return $this; }

    public function getAddressState(): string { return $this->addressState; }
    public function setAddressState(string $addressState): self { $this->addressState = $addressState; return $this; }

    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): self { $this->latitude = $latitude; return $this; }

    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): self { $this->longitude = $longitude; return $this; }

    public function getBudgetExpectation(): float { return $this->budgetExpectation; }
    public function setBudgetExpectation(float $budgetExpectation): self { $this->budgetExpectation = $budgetExpectation; return $this; }

    public function getDesiredDeadlineDays(): int { return $this->desiredDeadlineDays; }
    public function setDesiredDeadlineDays(int $desiredDeadlineDays): self { $this->desiredDeadlineDays = $desiredDeadlineDays; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getSelectedProviderId(): ?int { return $this->selectedProviderId; }
    public function setSelectedProviderId(?int $selectedProviderId): self { $this->selectedProviderId = $selectedProviderId; return $this; }

    public function getDateAdded(): string { return $this->dateAdded ?: date('Y-m-d H:i:s'); }
    public function setDateAdded(?string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }

    public function getDateModified(): string { return $this->dateModified ?: date('Y-m-d H:i:s'); }
    public function setDateModified(?string $dateModified): self { $this->dateModified = $dateModified; return $this; }
}
