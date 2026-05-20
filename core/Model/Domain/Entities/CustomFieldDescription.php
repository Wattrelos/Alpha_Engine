<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomFieldDescription - Tradução dos nomes e placeholders de campos personalizados.
 */
class CustomFieldDescription extends BaseEntity
{
    private int $customFieldId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: CustomField::class, foreignKey: 'customFieldId')]
    private ?CustomField $customField = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getCustomFieldId(): int { return $this->customFieldId; }
    public function setCustomFieldId(int $id): self { $this->customFieldId = $id; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $id): self { $this->languageId = $id; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getCustomField(): ?CustomField { return $this->customField; }
    public function setCustomField(?CustomField $field): self { $this->customField = $field; return $this; }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}