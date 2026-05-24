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
    private int $userId = 0;
    private string $ip = '';
    private string $userAgent = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: User::class, foreignKey: 'userId')]
    private ?User $user = null;

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $val): self { $this->userId = $val; return $this; }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
}