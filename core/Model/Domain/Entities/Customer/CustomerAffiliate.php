<?php
namespace Alpha\Model\Domain\Entities\Customer;

use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Mappers\CollectionToArrayConverter;

class CustomerAffiliate implements InterfaceEntity
{
    private int $customerId = 0;
    private string $company = '';
    private string $website = '';
    private string $tracking = '';
    private float $commission = 0.00;
    private string $tax = '';
    private string $paymentMethod = '';
    private string $cheque = '';
    private string $paypal = '';
    private string $bankName = '';
    private string $bankBranchNumber = '';
    private string $bankSwiftCode = '';
    private string $bankAccountName = '';
    private string $bankAccountNumber = '';
    private array $customField = [];
    private bool $status = false;
    private string $dateAdded = '';

    public function getId(): int
    {
        return $this->customerId;
    }

    public function setId(int $id): self
    {
        $this->customerId = $id;
        return $this;
    }

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $customerId): self { $this->customerId = $customerId; return $this; }

    public function getCompany(): string { return $this->company; }
    public function setCompany(string $company): self { $this->company = $company; return $this; }

    public function getWebsite(): string { return $this->website; }
    public function setWebsite(string $website): self { $this->website = $website; return $this; }

    public function getTracking(): string { return $this->tracking; }
    public function setTracking(string $tracking): self { $this->tracking = $tracking; return $this; }

    public function getCommission(): float { return $this->commission; }
    public function setCommission(float $commission): self { $this->commission = $commission; return $this; }

    public function getTax(): string { return $this->tax; }
    public function setTax(string $tax): self { $this->tax = $tax; return $this; }

    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function setPaymentMethod(string $paymentMethod): self { $this->paymentMethod = $paymentMethod; return $this; }

    public function getCheque(): string { return $this->cheque; }
    public function setCheque(string $cheque): self { $this->cheque = $cheque; return $this; }

    public function getPaypal(): string { return $this->paypal; }
    public function setPaypal(string $paypal): self { $this->paypal = $paypal; return $this; }

    public function getBankName(): string { return $this->bankName; }
    public function setBankName(string $bankName): self { $this->bankName = $bankName; return $this; }

    public function getBankBranchNumber(): string { return $this->bankBranchNumber; }
    public function setBankBranchNumber(string $bankBranchNumber): self { $this->bankBranchNumber = $bankBranchNumber; return $this; }

    public function getBankSwiftCode(): string { return $this->bankSwiftCode; }
    public function setBankSwiftCode(string $bankSwiftCode): self { $this->bankSwiftCode = $bankSwiftCode; return $this; }

    public function getBankAccountName(): string { return $this->bankAccountName; }
    public function setBankAccountName(string $bankAccountName): self { $this->bankAccountName = $bankAccountName; return $this; }

    public function getBankAccountNumber(): string { return $this->bankAccountNumber; }
    public function setBankAccountNumber(string $bankAccountNumber): self { $this->bankAccountNumber = $bankAccountNumber; return $this; }

    public function getCustomField(): string { return json_encode($this->customField); }
    public function getCustomFieldArray(): array { return $this->customField; }
    public function setCustomField(string|array $customField): self { 
        $this->customField = is_string($customField) ? (json_decode($customField, true) ?: []) : $customField; 
        return $this; 
    }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $status): self { $this->status = $status; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }

    public function jsonSerialize(): mixed
    {
        return CollectionToArrayConverter::convertEntity($this);
    }
}