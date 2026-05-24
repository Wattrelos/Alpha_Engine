<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Cart - Gerencia o estado do carrinho de compras.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Persistência Híbrida: Suporte a carrinhos vinculados a clientes logados ou sessões de visitantes.
 * - Integridade de Produto: Vinculação direta com Product e SubscriptionPlan via atributos ManyToOne.
 * - Tipagem Estrita: Quantidade e IDs tratados como inteiros para cálculos precisos no motor de checkout.
 * - Serialização: Campo 'option' tratado como string (geralmente JSON) para flexibilidade de variantes.
 * - Escudo Anti N+1: Relacionamentos pesados foram removidos da hidratação nativa. 
 *   O CartRepository orquestra os objetos ricos sob demanda.
 */
class Cart extends BaseEntity
{
    private int $storeId = 0;
    private int $customerId = 0;
    private string $sessionToken = '';
    private int $productId = 0;
    private int $subscriptionPlanId = 0;
    private string $option = '[]';
    private int $quantity = 0;
    private string $override = '';
    private float $price = 0.0;
    private string $dateAdded = '';

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $storeId): self { $this->storeId = $storeId; return $this; }

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $customerId): self { $this->customerId = $customerId; return $this; }

    public function getSessionToken(): string { return $this->sessionToken; }
    public function setSessionToken(string $sessionToken): self { $this->sessionToken = $sessionToken; return $this; }

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $productId): self { $this->productId = $productId; return $this; }

    public function getSubscriptionPlanId(): int { return $this->subscriptionPlanId; }
    public function setSubscriptionPlanId(int $subscriptionPlanId): self { $this->subscriptionPlanId = $subscriptionPlanId; return $this; }

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

    public function getOverride(): string { return $this->override; }
    public function setOverride(string $override): self { $this->override = $override; return $this; }

    public function getPrice(): float
    {
        return $this->price;
    }
    public function setPrice(float $price): self
    {
        $this->price = $price;
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
}
