<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ApiIp
 * Representa um endereço IP autorizado a consumir uma credencial de API (Whitelist).
 * 
 * @Table(name="api_ip")
 */
class ApiIp extends BaseEntity
{
    private int $apiId = 0;
    private string $ip = '';

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

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    public function getApi(): ?Api { return $this->api; }
    public function setApi(?Api $api): self { 
        $this->api = $api; return $this; 
    }
}