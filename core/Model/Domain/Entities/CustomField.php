<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade CustomField - Define campos personalizados para formulários do sistema.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Flexibilidade de Dados: Suporta diversos tipos (text, select, date, etc) com validação Regex.
 * - Tipagem PHP 8.4: Uso de bool para status e int para ordenação.
 * - Relacionamentos: #[OneToMany] para carregar descrições, valores e permissões por grupo.
 */
class CustomField extends BaseEntity
{
    private string $type = '';
    private string $value = '';
    private string $validation = '';
    private string $location = '';
    private bool $status = false;
    private int $sortOrder = 0;

    /** @var CustomFieldDescription[] */
    #[OneToMany(targetEntity: CustomFieldDescription::class, foreignKey: 'customFieldId')]
    private array $descriptions = [];

    /** @var CustomFieldValue[] */
    #[OneToMany(targetEntity: CustomFieldValue::class, foreignKey: 'customFieldId')]
    private array $values = [];

    /** @var CustomFieldCustomerGroup[] */
    #[OneToMany(targetEntity: CustomFieldCustomerGroup::class, foreignKey: 'customFieldId')]
    private array $customerGroups = [];

    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }

    public function getValue(): string { return $this->value; }
    public function setValue(string $value): self { $this->value = $value; return $this; }

    public function getValidation(): string { return $this->validation; }
    public function setValidation(string $validation): self { $this->validation = $validation; return $this; }

    public function getLocation(): string { return $this->location; }
    public function setLocation(string $location): self { $this->location = $location; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $status): self { $this->status = $status; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }

    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $descriptions): self { $this->descriptions = $descriptions; return $this; }

    public function getValues(): array { return $this->values; }
    public function setValues(array $values): self { $this->values = $values; return $this; }

    public function getCustomerGroups(): array { return $this->customerGroups; }
    public function setCustomerGroups(array $groups): self { $this->customerGroups = $groups; return $this; }
}