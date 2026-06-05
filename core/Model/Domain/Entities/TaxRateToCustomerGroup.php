<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Entities\Customer\CustomerGroup;

/**
 * Entidade TaxRateToCustomerGroup - Vincula taxas de impostos a grupos de clientes específicos.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Segmentação Fiscal: Permite que impostos variem conforme o perfil do cliente (Ex: B2B vs B2C).
 * - Tipagem PHP 8.4: IDs tipados como int para integridade referencial.
 * - Mapeamento Relacional: #[ManyToOne] para TaxRate e CustomerGroup.
 */
class TaxRateToCustomerGroup extends BaseEntity
{
    #[ManyToOne(targetEntity: TaxRate::class, foreignKey: 'taxRateId')]
    private ?TaxRate $taxRate = null;

    #[ManyToOne(targetEntity: CustomerGroup::class, foreignKey: 'customerGroupId')]
    private ?CustomerGroup $customerGroup = null;

    public function getTaxRateId(): int 
    { 
        return $this->taxRate ? (int)$this->taxRate->getId() : 0; 
    }
    
    public function setTaxRateId(int $id): self 
    { 
        if (!$this->taxRate) $this->taxRate = new TaxRate();
        $this->taxRate->setId($id); 
        return $this; 
    }

    public function getCustomerGroupId(): int 
    { 
        return $this->customerGroup ? (int)$this->customerGroup->getId() : 0; 
    }
    
    public function setCustomerGroupId(int $id): self 
    { 
        if (!$this->customerGroup) $this->customerGroup = new CustomerGroup();
        $this->customerGroup->setId($id); 
        return $this; 
    }

    public function getTaxRate(): ?TaxRate { return $this->taxRate; }
    public function setTaxRate(?TaxRate $taxRate): self { $this->taxRate = $taxRate; return $this; }

    public function getCustomerGroup(): ?CustomerGroup
    {
        return $this->customerGroup;
    }
    public function setCustomerGroup(?CustomerGroup $group): self { $this->customerGroup = $group; return $this; }
}