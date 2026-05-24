<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Session Entity - Alpha Engine
 * 
 * Representa o estado persistente de uma sessão no sistema.
 * Utiliza Surrogate Keys (id) e isola o identificador de transporte (sessionToken).
 */
class Session extends BaseEntity
{
    private string $sessionToken = '';
    private int $customerId = 0;
    private string $data = '';
    private string $expireAt = '';

    public function getSessionToken(): string
    {
        return $this->sessionToken;
    }

    public function setSessionToken(string $sessionToken): self
    {
        $this->sessionToken = $sessionToken;
        return $this;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getExpireAt(): string
    {
        return $this->expireAt;
    }

    public function setExpireAt(string $expireAt): self
    {
        $this->expireAt = $expireAt;
        return $this;
    }
}