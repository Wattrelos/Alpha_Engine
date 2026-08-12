<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade AttributeGroup
 * Classifica e agrupa os atributos (ex: Grupo "Hardware", Grupo "Conectividade").
 * 
 * @Table(name="attribute_group")
 */
class AttributeGroup extends BaseEntity
{
    private int $sortOrder = 0;

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $val): self { 
        $this->sortOrder = $val; 
        return $this; 
    }
}