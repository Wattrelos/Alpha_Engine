<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade CustomFieldValue - Define as opções para campos do tipo select, radio ou checkbox.
 */
class CustomFieldValue extends BaseEntity
{
    private int $customFieldId = 0;
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: CustomField::class, foreignKey: 'customFieldId')]
    private ?CustomField $customField = null;

    /** @var CustomFieldValueDescription[] */
    #[OneToMany(targetEntity: CustomFieldValueDescription::class, foreignKey: 'customFieldValueId')]
    private array $descriptions = [];

    public function getCustomFieldId(): int { return $this->customFieldId; }
    public function setCustomFieldId(int $id): self { $this->customFieldId = $id; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }

    public function getCustomField(): ?CustomField { return $this->customField; }
    public function setCustomField(?CustomField $field): self { $this->customField = $field; return $this; }

    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $descriptions): self { $this->descriptions = $descriptions; return $this; }

    public function addDescription(CustomFieldValueDescription $description): self
    {
        $this->descriptions[] = $description;
        return $this;
    }
}