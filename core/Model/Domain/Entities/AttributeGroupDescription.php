<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade AttributeGroupDescription - Traduções para o nome do grupo de atributos.
 */
class AttributeGroupDescription extends BaseEntity
{
    private int $attributeGroupId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: AttributeGroup::class, foreignKey: 'attributeGroupId')]
    private ?AttributeGroup $attributeGroup = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getAttributeGroupId(): int
    {
        return $this->attributeGroupId;
    }

    public function setAttributeGroupId(int $value): self
    {
        $this->attributeGroupId = $value;
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

    public function getAttributeGroup(): ?AttributeGroup
    {
        return $this->attributeGroup;
    }

    public function setAttributeGroup(?AttributeGroup $attributeGroup): self
    {
        $this->attributeGroup = $attributeGroup;
        return $this;
    }
}