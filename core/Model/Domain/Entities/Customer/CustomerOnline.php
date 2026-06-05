<?php

namespace Alpha\Model\Domain\Entities\Customer;

use Alpha\Model\Domain\BaseEntity;
use DateTimeImmutable;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerOnline
 * Rastreamento em tempo real de clientes navegando no site.
 * 
 * @Table(name="customer_online")
 */
class CustomerOnline extends BaseEntity
{
    private string $ip = '';
    private int $customerId = 0;
    private string $url = '';
    private string $referer = '';
    private ?DateTimeImmutable $dateAdded = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    /**
     * Apontamentos Técnicos:
     * 1. Analytics: Captura a URL atual e o referer para análise de tráfego interna.
     * 2. Performance: Geralmente limpa via cron para manter apenas os dados recentes.
     */

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;
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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getReferer(): string
    {
        return $this->referer;
    }

    public function setReferer(string $referer): self
    {
        $this->referer = $referer;
        return $this;
    }

    public function getDateAdded(): ?DateTimeImmutable
    {
        return $this->dateAdded;
    }

    public function setDateAdded(?DateTimeImmutable $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }
}