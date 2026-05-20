<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * ApiSession Entity - Alpha Engine
 * 
 * Segue o padrão de Surrogate Key (id) e isola o identificador de transporte (sessionToken).
 */
class ApiSession extends BaseEntity
{
    private int $apiId = 0;
    private string $sessionToken = '';
    private string $ip = '';
    private string $dateAdded = '';
    private string $dateModified = '';

    public function getApiId(): int
    {
        return $this->apiId;
    }

    public function setApiId(int $apiId): self
    {
        $this->apiId = $apiId;
        return $this;
    }

    public function getSessionToken(): string
    {
        return $this->sessionToken;
    }

    public function setSessionToken(string $sessionToken): self
    {
        $this->sessionToken = $sessionToken;
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

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getDateModified(): string
    {
        return $this->dateModified;
    }

    public function setDateModified(string $dateModified): self
    {
        $this->dateModified = $dateModified;
        return $this;
    }
}