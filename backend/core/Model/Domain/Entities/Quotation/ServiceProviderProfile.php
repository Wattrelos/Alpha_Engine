<?php

namespace Alpha\Model\Domain\Entities\Quotation;

use Alpha\Model\Domain\BaseEntity;

/**
 * ServiceProviderProfile - Perfil de credenciamento do prestador de serviços.
 * Atende aos requisitos RF033, RF034, RF035, RF036.
 */
class ServiceProviderProfile extends BaseEntity
{
    private int $customerId = 0;
    private ?string $companyName = null;
    private ?string $tradeName = null;
    private string $documentNumber = '';
    private ?string $specialties = null;
    private float $serviceRadiusKm = 25.0;
    private ?float $latitude = null;
    private ?float $longitude = null;
    private ?string $addressCep = null;
    private ?string $addressCity = null;
    private ?string $addressState = null;
    private float $rating = 5.0;
    private int $totalReviews = 0;
    private bool $status = true;
    private ?string $dateAdded = null;
    private ?string $dateModified = null;

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $customerId): self { $this->customerId = $customerId; return $this; }

    public function getCompanyName(): ?string { return $this->companyName; }
    public function setCompanyName(?string $companyName): self { $this->companyName = $companyName; return $this; }

    public function getTradeName(): ?string { return $this->tradeName; }
    public function setTradeName(?string $tradeName): self { $this->tradeName = $tradeName; return $this; }

    public function getDocumentNumber(): string { return $this->documentNumber; }
    public function setDocumentNumber(string $documentNumber): self { $this->documentNumber = $documentNumber; return $this; }

    public function getSpecialties(): ?string { return $this->specialties; }
    public function setSpecialties(?string $specialties): self { $this->specialties = $specialties; return $this; }

    public function getServiceRadiusKm(): float { return $this->serviceRadiusKm; }
    public function setServiceRadiusKm(float $serviceRadiusKm): self { $this->serviceRadiusKm = $serviceRadiusKm; return $this; }

    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): self { $this->latitude = $latitude; return $this; }

    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): self { $this->longitude = $longitude; return $this; }

    public function getAddressCep(): ?string { return $this->addressCep; }
    public function setAddressCep(?string $addressCep): self { $this->addressCep = $addressCep; return $this; }

    public function getAddressCity(): ?string { return $this->addressCity; }
    public function setAddressCity(?string $addressCity): self { $this->addressCity = $addressCity; return $this; }

    public function getAddressState(): ?string { return $this->addressState; }
    public function setAddressState(?string $addressState): self { $this->addressState = $addressState; return $this; }

    public function getRating(): float { return $this->rating; }
    public function setRating(float $rating): self { $this->rating = $rating; return $this; }

    public function getTotalReviews(): int { return $this->totalReviews; }
    public function setTotalReviews(int $totalReviews): self { $this->totalReviews = $totalReviews; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $status): self { $this->status = $status; return $this; }

    public function getDateAdded(): string { return $this->dateAdded ?: date('Y-m-d H:i:s'); }
    public function setDateAdded(?string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }

    public function getDateModified(): string { return $this->dateModified ?: date('Y-m-d H:i:s'); }
    public function setDateModified(?string $dateModified): self { $this->dateModified = $dateModified; return $this; }
}
