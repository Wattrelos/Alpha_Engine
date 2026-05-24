<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class CategoryPath extends BaseEntity
{
    #[ManyToOne(targetEntity: Category::class)]
    private ?Category $category = null;

    private int $pathId = 0;
    private int $level = 0;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getPathId(): int
    {
        return $this->pathId;
    }

    public function setPathId(int $pathId): self
    {
        $this->pathId = $pathId;
        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;
        return $this;
    }
}