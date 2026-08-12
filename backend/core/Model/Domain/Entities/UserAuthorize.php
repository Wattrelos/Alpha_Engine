<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade UserAuthorize
 * Armazena as autorizações de dispositivos/sessões de administradores (painel admin).
 * 
 * @Table(name="user_authorize")
 */
class UserAuthorize extends BaseEntity
{
    private int $userId = 0;
    private string $token = '';
    private int $total = 0;
    private string $ip = '';
    private string $userAgent = '';
    private bool $status = false;
    private string $dateAdded = '';
    private string $dateExpire = '';

    #[ManyToOne(targetEntity: User::class, foreignKey: 'userId')]
    private ?User $user = null;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;
        return $this;
    }

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

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;
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

    public function getDateExpire(): string
    {
        return $this->dateExpire;
    }

    public function setDateExpire(string $dateExpire): self
    {
        $this->dateExpire = $dateExpire;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
