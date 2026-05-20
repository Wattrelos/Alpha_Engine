<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductSubscription
 * Define preços de assinatura para produtos vinculados a planos.
 * 
 * @Table(name="product_subscription")
 */
class ProductSubscription extends BaseEntity
{
    private int $productId = 0;
    private int $subscriptionPlanId = 0;
    private int $customerGroupId = 0;
    private float $trialPrice = 0.0000;
    private float $price = 0.0000;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: SubscriptionPlan::class, foreignKey: 'subscriptionPlanId')]
    private ?SubscriptionPlan $subscriptionPlan = null;

    #[ManyToOne(targetEntity: CustomerGroup::class, foreignKey: 'customerGroupId')]
    private ?CustomerGroup $customerGroup = null;

    public function getTrialPrice(): float
    {
        return $this->trialPrice;
    }

    public function setTrialPrice(float $trialPrice): self
    {
        $this->trialPrice = $trialPrice;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getSubscriptionPlanId(): int
    {
        return $this->subscriptionPlanId;
    }

    public function setSubscriptionPlanId(int $subscriptionPlanId): self
    {
        $this->subscriptionPlanId = $subscriptionPlanId;
        return $this;
    }

    public function getCustomerGroupId(): int
    {
        return $this->customerGroupId;
    }

    public function setCustomerGroupId(int $customerGroupId): self
    {
        $this->customerGroupId = $customerGroupId;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getSubscriptionPlan(): ?SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }
}
