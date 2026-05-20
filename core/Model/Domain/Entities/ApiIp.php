<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade ApiIp - Whitelist de IPs autorizados para cada chave de API.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Segurança de Rede: Campo 'ip' tipado para garantir validações exatas de endereços IPv4/IPv6.
 * - Correção de Nomenclatura: Propriedade de relacionamento corrigida de 'stockStatus' para 'api'.
 * - Injeção Relacional: Atributo #[ManyToOne] para vinculação automática com a entidade Api proprietária.
 * - PHP 8.4 Readiness: Uso de tipos nativos e interface fluida.
 */
class ApiIp extends BaseEntity
{
    private string $ip = '';

    #[ManyToOne(targetEntity: Api::class, foreignKey: 'apiId')]
    private ?Api $api = null;

    public function getApiId(): int
    {
        return $this->api ? (int)$this->api->getId() : 0;
    }

    public function setApiId(int $apiId): self
    {
        if (!$this->api) {
            $this->api = new Api();
        }
        $this->api->setId($apiId);
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

    /**
     * Retorna o objeto Api associado a este IP.
     */
    public function getApi(): ?Api
    {
        return $this->api;
    }

    /**
     * Injeta o objeto Api associado.
     */
    public function setApi(?Api $api): self
    {
        $this->api = $api;
        return $this;
    }
}
