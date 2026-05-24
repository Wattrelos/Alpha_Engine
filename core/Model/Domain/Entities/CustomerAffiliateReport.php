<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

// @ORM\Entity
/* @ORM\Table(
*     name="CustomerAffiliateReport",
*     indexes={@ORM\Index(name="customerId", columns={"customerId"}),@ORM\Index(name="storeId", columns={"storeId"})}
* )
*/
class CustomerAffiliateReport extends BaseEntity
{

   /**
     * Entidade CustomerAffiliateReport - Relatórios de cliques e tráfego de afiliados.
     * 
     * Melhoras aplicadas (Alpha Engine):
     * - Refatoração Completa: Remoção do lixo legado (Anotações do Doctrine).
     * - Tipagem Estrita: Propriedades fortemente tipadas para PHP 8.4.
     * - Relacionamentos: #[ManyToOne] para resolver os IDs de Cliente e Loja sem joins manuais.
     */

    private int $customerId = 0;
    private int $storeId = 0;
    private string $ip = '';
    private string $country = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $customerId): self { $this->customerId = $customerId; return $this; }

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $storeId): self { $this->storeId = $storeId; return $this; }

    public function getIp(): string { return $this->ip; }
    public function setIp(string $ip): self { $this->ip = $ip; return $this; }

    public function getCountry(): string { return $this->country; }
    public function setCountry(string $country): self { $this->country = $country; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }

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
