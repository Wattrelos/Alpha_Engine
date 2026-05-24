<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class CategoryFilter extends BaseEntity
{
    #[ManyToOne(targetEntity: Category::class)]
    private ?Category $category = null;

    #[ManyToOne(targetEntity: Filter::class)]
    private ?Filter $filter = null;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

    public function setFilter(?Filter $filter): self
    {
        $this->filter = $filter;
        return $this;
    }
}