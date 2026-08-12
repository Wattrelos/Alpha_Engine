<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Attribute
 * Representa uma especificação técnica isolada (ex: "Memória RAM", "Bluetooth").
 * 
 * @Table(name="attribute")
 */
class Attribute extends BaseEntity
{
    private int $attributeGroupId = 0;
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: AttributeGroup::class, foreignKey: 'attributeGroupId')]
    private ?AttributeGroup $attributeGroup = null;

    public function getAttributeGroupId(): int { return $this->attributeGroupId; }
    public function setAttributeGroupId(int $val): self { 
        $this->attributeGroupId = $val; 
        return $this; 
    }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $val): self { 
        $this->sortOrder = $val; 
        return $this; 
    }

    public function getAttributeGroup(): ?AttributeGroup { return $this->attributeGroup; }
    public function setAttributeGroup(?AttributeGroup $val): self { 
        $this->attributeGroup = $val; return $this; 
    }
}