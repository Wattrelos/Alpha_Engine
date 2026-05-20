<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade SubscriptionTransaction - Registra os pagamentos e cobranças de uma assinatura.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Integridade Financeira: Uso de float para amount garantindo precisão decimal.
 * - Identificação Externa: Campo transactionId para vincular ao gateway de pagamento.
 * - Relacionamentos: #[ManyToOne] para vincular a transação à Subscription e ao Order original.
 */
class SubscriptionTransaction extends BaseEntity
{
    private string $transactionId = '';
    private float $amount = 0.0;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Subscription::class, foreignKey: 'subscriptionId')]
    private ?Subscription $subscription = null;

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    public function getSubscriptionId(): int 
    { 
        return $this->subscription ? (int)$this->subscription->getId() : 0; 
    }
    
    public function setSubscriptionId(int $id): self 
    { 
        if (!$this->subscription) $this->subscription = new Subscription();
        $this->subscription->setId($id); 
        return $this; 
    }

    public function getOrderId(): int 
    { 
        return $this->order ? (int)$this->order->getId() : 0; 
    }
    
    public function setOrderId(int $id): self 
    { 
        if (!$this->order) $this->order = new Order();
        $this->order->setId($id); return $this; 
    }

    public function getTransactionId(): string { return $this->transactionId; }
    public function setTransactionId(string $id): self { $this->transactionId = $id; return $this; }

    public function getAmount(): float { return $this->amount; }
    public function setAmount(float $amount): self { $this->amount = $amount; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $date): self { $this->date = $date; return $this; }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;
        return $this;
    }
}