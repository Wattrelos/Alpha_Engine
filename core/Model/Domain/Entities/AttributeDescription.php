<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade AttributeDescription - Traduções para o nome do atributo técnico.
 */
class AttributeDescription extends BaseEntity
{
    private int $attributeId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Attribute::class, foreignKey: 'attributeId')]
    private ?Attribute $attribute = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getAttributeId(): int
    {
        return $this->attributeId;
    }

    public function setAttributeId(int $value): self
    {
        $this->attributeId = $value;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $value): self
    {
        $this->languageId = $value;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;
        return $this;
    }
}