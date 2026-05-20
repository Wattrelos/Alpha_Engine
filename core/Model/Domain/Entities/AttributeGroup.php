<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade AttributeGroup - Agrupador de atributos técnicos (ex: Especificações de Hardware).
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Estrutura Hierárquica: Centraliza a gestão de especificações, permitindo que o DAO carregue todos os atributos filhos e suas traduções em cascata.
 * - Tipagem PHP 8.4: Uso de 'int' nativo para sortOrder, garantindo ordenação consistente no motor de renderização.
 * - Interface Fluida: Setters preparados para encadeamento, otimizando a criação de fixtures e objetos de teste.
 */
class AttributeGroup extends BaseEntity
{
    private int $sortOrder = 0;

    /**
     * @var AttributeGroupDescription[]
     */
    #[OneToMany(targetEntity: AttributeGroupDescription::class, mappedBy: "attributeGroup", foreignKey: "attributeGroupId")]
    private array $descriptions = [];

    /**
     * @var Attribute[]
     */
    #[OneToMany(targetEntity: Attribute::class, mappedBy: "attributeGroup", foreignKey: "attributeGroupId")]
    private array $attributes = [];

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
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

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }
}