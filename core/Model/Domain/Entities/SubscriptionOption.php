<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade SubscriptionOption
 * 
 * @Table(name="subscription_option")
 */
class SubscriptionOption extends BaseEntity
{
    private int $subscriptionId = 0;
    private int $subscriptionProductId = 0;
    private int $productOptionId = 0;
    private int $productOptionValueId = 0;
    private string $name = '';
    private string $value = '';
    private string $type = '';

    #[ManyToOne(targetEntity: Subscription::class, foreignKey: 'subscriptionId')]
    private ?Subscription $subscription = null;

    #[ManyToOne(targetEntity: SubscriptionProduct::class, foreignKey: 'subscriptionProductId')]
    private ?SubscriptionProduct $subscriptionProduct = null;

    #[ManyToOne(targetEntity: ProductOption::class, foreignKey: 'productOptionId')]
    private ?ProductOption $productOption = null;

    #[ManyToOne(targetEntity: ProductOptionValue::class, foreignKey: 'productOptionValueId')]
    private ?ProductOptionValue $productOptionValue = null;

    public function getSubscriptionId(): int
    {
        return $this->subscriptionId;
    }

    public function setSubscriptionId(int $subscriptionId): self
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    public function getSubscriptionProductId(): int
    {
        return $this->subscriptionProductId;
    }

    public function setSubscriptionProductId(int $subscriptionProductId): self
    {
        $this->subscriptionProductId = $subscriptionProductId;
        return $this;
    }

    public function getProductOptionId(): int
    {
        return $this->productOptionId;
    }

    public function setProductOptionId(int $productOptionId): self
    {
        $this->productOptionId = $productOptionId;
        return $this;
    }

    public function getProductOptionValueId(): int
    {
        return $this->productOptionValueId;
    }

    public function setProductOptionValueId(int $productOptionValueId): self
    {
        $this->productOptionValueId = $productOptionValueId;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function getSubscriptionProduct(): ?SubscriptionProduct
    {
        return $this->subscriptionProduct;
    }

    public function setSubscriptionProduct(?SubscriptionProduct $subscriptionProduct): self
    {
        $this->subscriptionProduct = $subscriptionProduct;
        return $this;
    }

    public function getProductOption(): ?ProductOption
    {
        return $this->productOption;
    }

    public function setProductOption(?ProductOption $productOption): self
    {
        $this->productOption = $productOption;
        return $this;
    }

    public function getProductOptionValue(): ?ProductOptionValue
    {
        return $this->productOptionValue;
    }

    public function setProductOptionValue(?ProductOptionValue $productOptionValue): self
    {
        $this->productOptionValue = $productOptionValue;
        return $this;
    }
}