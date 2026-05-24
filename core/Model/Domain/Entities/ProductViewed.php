<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
/**
 * Entidade ProductViewed
 * Contador simples de visualizações por produto.
 * 
 * @Table(name="product_viewed")
 */
class ProductViewed extends BaseEntity
{
    private int $viewed = 0;

    public function getViewed(): int
    {
        return $this->viewed;
    }

    public function setViewed(int $viewed): self
    {
        $this->viewed = $viewed;
        return $this;
    }
}
