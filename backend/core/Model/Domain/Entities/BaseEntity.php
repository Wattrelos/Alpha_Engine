<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Mappers\CollectionToArrayConverter;

abstract class BaseEntity implements InterfaceEntity
{
    protected int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return CollectionToArrayConverter::convertEntity($this);
    }
}