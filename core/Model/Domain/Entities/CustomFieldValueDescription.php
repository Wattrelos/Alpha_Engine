<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomFieldValueDescription - Tradução para as opções de valores de campos personalizados.
 */
class CustomFieldValueDescription extends BaseEntity
{
    private int $customFieldValueId = 0;
    private int $languageId = 0;
    private int $customFieldId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: CustomFieldValue::class, foreignKey: 'customFieldValueId')]
    private ?CustomFieldValue $customFieldValue = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getCustomFieldValueId(): int { return $this->customFieldValueId; }
    public function setCustomFieldValueId(int $id): self { $this->customFieldValueId = $id; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $id): self { $this->languageId = $id; return $this; }

    public function getCustomFieldId(): int { return $this->customFieldId; }
    public function setCustomFieldId(int $id): self { $this->customFieldId = $id; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getCustomFieldValue(): ?CustomFieldValue { return $this->customFieldValue; }
    public function setCustomFieldValue(?CustomFieldValue $value): self { $this->customFieldValue = $value; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}