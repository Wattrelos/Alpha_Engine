<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Banner - Representa um agrupador de imagens de destaque ou carrossel na Alpha Engine.
 */
class Banner extends BaseEntity
{
    private string $name = '';
    private bool $status = true;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool|int $status): self
    {
        $this->status = (bool)$status;
        return $this;
    }
}