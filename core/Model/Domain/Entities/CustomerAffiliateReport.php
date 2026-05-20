<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

// @ORM\Entity
/* @ORM\Table(
*     name="CustomerAffiliateReport",
*     indexes={@ORM\Index(name="customerId", columns={"customerId"}),@ORM\Index(name="storeId", columns={"storeId"})}
* )
*/
class CustomerAffiliateReport extends BaseEntity
{

    // @ORM\Column(type="string", length=40, nullable=false)
    private $ip;
    // @ORM\Column(type="string", length=2, nullable=false)
    private $country;
    // @ORM\Column(type="date", nullable=false)
    private $dateAdded;
// Corrigido! Exemplo ------------------------------------------------------------------------------------------
    // @ORM\ManyToOne(targetEntity=\Customer::class, inversedBy="customerAffiliateReports")
    // @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="restrict")
    private $customer;
// Corrigido! Exemplo ------------------------------------------------------------------------------------------

    // @ORM\ManyToOne(targetEntity=\Store::class, inversedBy="CustomerAffiliateReports")
    // @ORM\JoinColumn(name="storeId", referencedColumnName="storeId", nullable=false, onDelete="restrict")
    // private $Store;


    public function getIp()
    {
        return $this->ip;
    }
    public function setIp($value)
    {
        $this->ip = $value;
        return $this;
    }
    public function getCountry()
    {
        return $this->country;
    }
    public function setCountry($value)
    {
        $this->country = $value;
        return $this;
    }
    public function getDateAdded()
    {
        return $this->dateAdded;
    }
    public function setDateAdded($value)
    {
        $this->dateAdded = $value;
        return $this;
    }
    public function getCustomer()
    {
        return $this->Customer;
    }
    public function setCustomer($value)
    {
        $this->Customer = $value;
        return $this;
    }
    public function getStore()
    {
        return $this->Store;
    }
    public function setStore($value)
    {
        $this->Store = $value;
        return $this;
    }
}
