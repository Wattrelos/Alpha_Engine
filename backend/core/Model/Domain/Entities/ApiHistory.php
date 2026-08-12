<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ApiHistory
 * Registra a trilha de auditoria e consumo dos endpoints da API.
 * 
 * @Table(name="api_history")
 */
class ApiHistory extends BaseEntity
{
    private int $apiId = 0;
    private string $call = '';
    private string $ip = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Api::class, foreignKey: 'apiId')]
    private ?Api $api = null;

    public function getApiId(): int
    {
        return $this->apiId;
    }

    public function setApiId(int $apiId): self
    {
        $this->apiId = $apiId;
        return $this;
    }

    public function getCall(): string
    {
        return $this->call;
    }

    public function setCall(string $call): self
    {
        $this->call = $call;
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

    public function getApi(): ?Api { return $this->api; }
    public function setApi(?Api $api): self { $this->api = $api; return $this; }
}