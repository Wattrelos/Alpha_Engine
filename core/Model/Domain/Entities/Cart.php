<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Cart - Gerencia o estado do carrinho de compras.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Persistência Híbrida: Suporte a carrinhos vinculados a clientes logados ou sessões de visitantes.
 * - Integridade de Produto: Vinculação direta com Product e SubscriptionPlan via atributos ManyToOne.
 * - Tipagem Estrita: Quantidade e IDs tratados como inteiros para cálculos precisos no motor de checkout.
 * - Serialização: Campo 'option' tratado como string (geralmente JSON) para flexibilidade de variantes.
 */
class Cart extends BaseEntity
{
    private string $sessionId = '';
    private string $option = '';
    private int $quantity = 0;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Api::class, foreignKey: 'apiId')]
    private ?Api $api = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: SubscriptionPlan::class, foreignKey: 'subscriptionPlanId')]
    private ?SubscriptionPlan $subscriptionPlan = null;

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

    public function getSessionId(): string { return $this->sessionId; }
    public function setSessionId(string $sessionId): self { $this->sessionId = $sessionId; return $this; }

    public function getProductId(): int 
    { 
        return $this->product ? (int)$this->product->getId() : 0; 
    }
    
    public function setProductId(int $productId): self 
    { 
        if (!$this->product) {
            $this->product = new Product();
        }
        $this->product->setId($productId); 
        return $this; 
    }

    public function getSubscriptionPlanId(): int 
    { 
        return $this->subscriptionPlan ? (int)$this->subscriptionPlan->getId() : 0; 
    }
    
    public function setSubscriptionPlanId(int $subscriptionPlanId): self 
    { 
        if (!$this->subscriptionPlan) {
            $this->subscriptionPlan = new SubscriptionPlan();
        }
        $this->subscriptionPlan->setId($subscriptionPlanId); 
        return $this; 
    }

    public function getOption(): string
    {
        return $this->option;
    }

    public function setOption(string $value): self
    {
        $this->option = $value;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $value): self
    {
        $this->quantity = $value;
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

    public function getApi(): ?Api { return $this->api; }
    public function setApi(?Api $api): self { $this->api = $api; return $this; }

    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }

    public function getSubscriptionPlan(): ?SubscriptionPlan { return $this->subscriptionPlan; }
    public function setSubscriptionPlan(?SubscriptionPlan $subscriptionPlan): self { $this->subscriptionPlan = $subscriptionPlan; return $this; }
}
