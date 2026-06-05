<?php
namespace Alpha\Model\Domain\Entities\Customer;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerActivity - Registra o histórico de ações realizadas pelo cliente.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Rastreabilidade: Monitoramento de ações (key) e dados associados (data) para auditoria de comportamento.
 * - Segurança de Rede: Registro de IP tipado para análise de segurança e prevenção de fraudes.
 * - Injeção Relacional: Atributo #[ManyToOne] para que o DAO vincule a atividade ao Cliente proprietário.
 * - PHP 8.4 Readiness: Uso de tipos nativos e interface fluida.
 */
class CustomerActivity extends BaseEntity
{
    private int $customerId = 0;
    private string $key = '';
    private string $data = '';
    private string $ip = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $value): self
    {
        $this->key = $value;
        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $value): self
    {
        $this->data = $value;
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

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $value): self
    {
        $this->dateAdded = $value;
        return $this;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $value): self
    {
        $this->customerId = $value;
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
}
