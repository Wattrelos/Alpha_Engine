<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade UserLogin
 * Histórico e registro de tentativas de login de usuários (Backoffice).
 * 
 * @Table(name="user_login")
 */
class UserLogin extends BaseEntity
{
    private string $username = '';
    private string $ip = '';
    private int $total = 1;
    private string $dateAdded = '';
    private string $dateModified = '';

    public function getUsername(): string { return $this->username; }
    public function setUsername(string $val): self { $this->username = $val; return $this; }

    public function getIp(): string { return $this->ip; }
    public function setIp(string $ip): self { $this->ip = $ip; return $this; }

    public function getTotal(): int { return $this->total; }
    public function setTotal(int $total): self { $this->total = $total; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }

    public function getDateModified(): string { return $this->dateModified; }
    public function setDateModified(string $dateModified): self { $this->dateModified = $dateModified; return $this; }
}