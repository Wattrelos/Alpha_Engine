<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade OrderSubscription
 * Associa os planos de assinaturas/recorrência selecionados em um pedido específico.
 * 
 * @Table(name="order_subscription")
 */
class OrderSubscription extends BaseEntity
{
    private int $orderProductId = 0;
    private int $orderId = 0;
    private int $productId = 0;
    private int $quantity = 1;
    private int $subscriptionPlanId = 0;
    private float $trialPrice = 0.0;
    private float $trialTax = 0.0;
    private string $trialFrequency = '';
    private int $trialCycle = 0;
    private int $trialDuration = 0;
    private bool $trialStatus = false;
    private float $price = 0.0;
    private float $tax = 0.0;
    private string $frequency = '';
    private int $cycle = 1;
    private int $duration = 0;

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    #[ManyToOne(targetEntity: OrderProduct::class, foreignKey: 'orderProductId')]
    private ?OrderProduct $orderProduct = null;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: SubscriptionPlan::class, foreignKey: 'subscriptionPlanId')]
    private ?SubscriptionPlan $subscriptionPlan = null;

    public function getOrderProductId(): int { return $this->orderProductId; }
    public function setOrderProductId(int $val): self { $this->orderProductId = $val; return $this; }

    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $val): self { $this->orderId = $val; return $this; }

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $val): self { $this->productId = $val; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $val): self { $this->quantity = $val; return $this; }

    public function getSubscriptionPlanId(): int { return $this->subscriptionPlanId; }
    public function setSubscriptionPlanId(int $val): self { $this->subscriptionPlanId = $val; return $this; }

    public function getTrialPrice(): float { return $this->trialPrice; }
    public function setTrialPrice(float $val): self { $this->trialPrice = $val; return $this; }

    public function getTrialTax(): float { return $this->trialTax; }
    public function setTrialTax(float $val): self { $this->trialTax = $val; return $this; }

    public function getTrialFrequency(): string { return $this->trialFrequency; }
    public function setTrialFrequency(string $val): self { $this->trialFrequency = $val; return $this; }

    public function getTrialCycle(): int { return $this->trialCycle; }
    public function setTrialCycle(int $val): self { $this->trialCycle = $val; return $this; }

    public function getTrialDuration(): int { return $this->trialDuration; }
    public function setTrialDuration(int $val): self { $this->trialDuration = $val; return $this; }

    public function isTrialStatus(): bool { return $this->trialStatus; }
    public function setTrialStatus(bool $val): self { $this->trialStatus = $val; return $this; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $val): self { $this->price = $val; return $this; }

    public function getTax(): float { return $this->tax; }
    public function setTax(float $val): self { $this->tax = $val; return $this; }

    public function getFrequency(): string { return $this->frequency; }
    public function setFrequency(string $val): self { $this->frequency = $val; return $this; }

    public function getCycle(): int { return $this->cycle; }
    public function setCycle(int $val): self { $this->cycle = $val; return $this; }

    public function getDuration(): int { return $this->duration; }
    public function setDuration(int $val): self { $this->duration = $val; return $this; }
}