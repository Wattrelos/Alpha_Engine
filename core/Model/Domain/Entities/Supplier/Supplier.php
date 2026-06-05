<?php

namespace Alpha\Model\Domain\Entities\Supplier;

use Alpha\Model\Domain\Entities\BaseEntity;

class Supplier extends BaseEntity
{
    public const TABLE_NAME = 'agsc_suppliers';

    // Como esta classe fica em uma subpasta, temos que utilizar alias para o namespace, por exemplo:
    // use Alpha\Model\Domain\Entities\Supplier\Supplier as SupplierSupplier;
    // Isso ocorre porque estamos utilizando o namespace Alpha\Model\Domain\Entities\Supplier na classe Supplier
    // E a classe Supplier também está no mesmo namespace, então para evitar conflito, usamos o alias.

    // Agora, precisamos ajusta os atributos conforme a tabela: `tbkk_suppliers`(`id`, `company_name`, `trade_name`, `tax_id`, `state_registration`, `municipal_registration`, `email`, `phone`, `website`, `is_active`, `created_at`, `updated_at`)
    // private int $id; Este attributo é herdado de baseEntity
    private string $companyName = '';
    private string $tradeName = '';
    private string $taxId = '';
    private string $stateRegistration = '';
    private string $municipalRegistration = '';
    private string $email = '';
    private string $phone = '';
    private string $website = '';
    private bool   $isActive = true;
    private string $createdAt = '';
    private string $updatedAt = '';

    // Alpha Engine: Associação estruturada com a entidade dependente
    private ?Addresses $addresses = null;

    public function __construct() {}

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): void
    {
        $this->companyName = $companyName;
    }

    public function getTradeName(): string
    {
        return $this->tradeName;
    }

    public function setTradeName(string $tradeName): void
    {
        $this->tradeName = $tradeName;
    }

    public function getTaxId(): string
    {
        return $this->taxId;
    }

    public function setTaxId(string $taxId): void
    {
        $this->taxId = $taxId;
    }

    public function getStateRegistration(): string
    {
        return $this->stateRegistration;
    }

    public function setStateRegistration(string $stateRegistration): void
    {
        $this->stateRegistration = $stateRegistration;
    }

    public function getMunicipalRegistration(): string
    {
        return $this->municipalRegistration;
    }

    public function setMunicipalRegistration(string $municipalRegistration): void
    {
        $this->municipalRegistration = $municipalRegistration;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function setWebsite(string $website): void
    {
        $this->website = $website;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getAddresses(): ?Addresses
    {
        return $this->addresses;
    }

    public function setAddresses(?Addresses $addresses): void
    {
        $this->addresses = $addresses;
    }
}
