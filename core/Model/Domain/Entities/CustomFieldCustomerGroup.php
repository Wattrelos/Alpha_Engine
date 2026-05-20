<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomFieldCustomerGroup - Define se um campo é obrigatório para um grupo de clientes.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Regras de Negócio: Permite que campos sejam opcionais para B2C mas obrigatórios para B2B.
 * - Mapeamento Relacional: #[ManyToOne] para vincular ao CustomField e CustomerGroup.
 */
class CustomFieldCustomerGroup extends BaseEntity
{
    private int $customFieldId = 0;
    private int $customerGroupId = 0;
    private bool $required = false;

    #[ManyToOne(targetEntity: CustomField::class, foreignKey: 'customFieldId')]
    private ?CustomField $customField = null;

    #[ManyToOne(targetEntity: CustomerGroup::class, foreignKey: 'customerGroupId')]
    private ?CustomerGroup $customerGroup = null;

    public function getCustomFieldId(): int { return $this->customFieldId; }
    public function setCustomFieldId(int $id): self { $this->customFieldId = $id; return $this; }

    public function getCustomerGroupId(): int { return $this->customerGroupId; }
    public function setCustomerGroupId(int $id): self { $this->customerGroupId = $id; return $this; }

    public function isRequired(): bool { return $this->required; }
    public function setRequired(bool $required): self { $this->required = $required; return $this; }

    public function getCustomField(): ?CustomField { return $this->customField; }
    public function setCustomField(?CustomField $field): self { $this->customField = $field; return $this; }

    public function getCustomerGroup(): ?CustomerGroup { return $this->customerGroup; }
    public function setCustomerGroup(?CustomerGroup $group): self { $this->customerGroup = $group; return $this; }
}