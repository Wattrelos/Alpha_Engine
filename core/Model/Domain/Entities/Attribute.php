<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Attribute - Representa um atributo técnico individual.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Mapeamento de Hierarquia: Vinculação explícita ao AttributeGroup e coleções de descrições.
 * - Segurança Relacional: Uso de foreignKey no atributo ManyToOne para automação total de chaves estrangeiras pelo DAO.
 */
class Attribute extends BaseEntity
{
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: AttributeGroup::class, foreignKey: 'attributeGroupId')]
    private ?AttributeGroup $attributeGroup = null;

    /**
     * @var AttributeDescription[]
     */
    #[OneToMany(targetEntity: AttributeDescription::class, mappedBy: "attribute", foreignKey: "attributeId")]
    private array $descriptions = [];

    public function getAttributeGroupId(): int
    {
        return $this->attributeGroup ? (int)$this->attributeGroup->getId() : 0;
    }

    public function setAttributeGroupId(int $value): self
    {
        if (!$this->attributeGroup) {
            $this->attributeGroup = new AttributeGroup();
        }
        $this->attributeGroup->setId($value);
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
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

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $descriptions): self
    {
        $this->descriptions = $descriptions;
        return $this;
    }
}