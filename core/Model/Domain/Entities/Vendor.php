<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Vendor - Gerencia parceiros ou vendedores externos (Marketplace).
 */
class Vendor extends BaseEntity
{
    private string $name = '';
    private string $email = '';
    private bool $status = false;

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self { $this->status = $status; return $this; }
}