<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerIp - Rastreia os endereços IP utilizados pelo cliente para acesso.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Segurança e Auditoria: Registro sistemático de IPs para prevenção de fraudes e análise de acessos.
 * - Contexto Multi-loja: Vinculação com a Store para identificar a origem do acesso no ecossistema.
 * - Injeção Relacional: Atributos #[ManyToOne] para resolução automática de Customer e Store pelo DAO.
 * - PHP 8.4 Readiness: Tipos nativos rigorosos e interface fluida.
 */
class CustomerIp extends BaseEntity
{
    private string $ip = '';
    private string $country = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    public function getCustomerId(): int
    {
        return $this->customer ? (int)$this->customer->getId() : 0;
    }

    public function setCustomerId(int $customerId): self
    {
        if (!$this->customer) {
            $this->customer = new Customer();
        }
        $this->customer->setId($customerId);
        return $this;
    }

    public function getStoreId(): int
    {
        return $this->store ? (int)$this->store->getId() : 0;
    }

    public function setStoreId(int $storeId): self
    {
        if (!$this->store) {
            $this->store = new Store();
        }
        $this->store->setId($storeId);
        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $value): self
    {
        $this->ip = $value;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $value): self
    {
        $this->country = $value;
        return $this;
    }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $value): self
    {
        $this->dateAdded = $value;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }
}
