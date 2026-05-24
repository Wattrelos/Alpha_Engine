<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class ProductSubscription extends BaseEntity
{
    #[ManyToOne(targetEntity: Product::class)]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: SubscriptionPlan::class)]
    private ?SubscriptionPlan $subscriptionPlan = null;

    #[ManyToOne(targetEntity: CustomerGroup::class)]
    private ?CustomerGroup $customerGroup = null;

    private float $trialPrice = 0.0;
    private float $price = 0.0;

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getSubscriptionPlan(): ?SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    public function setSubscriptionPlan(?SubscriptionPlan $subscriptionPlan): self
    {
        $this->subscriptionPlan = $subscriptionPlan;
        return $this;
    }

    public function getCustomerGroup(): ?CustomerGroup
    {
        return $this->customerGroup;
    }

    public function setCustomerGroup(?CustomerGroup $customerGroup): self
    {
        $this->customerGroup = $customerGroup;
        return $this;
    }

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
}