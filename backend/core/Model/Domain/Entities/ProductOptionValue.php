<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class ProductOptionValue extends BaseEntity
{
    #[ManyToOne(targetEntity: ProductOption::class)]
    private ?ProductOption $productOption = null;

    #[ManyToOne(targetEntity: Product::class)]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Option::class)]
    private ?Option $option = null;

    #[ManyToOne(targetEntity: OptionValue::class)]
    private ?OptionValue $optionValue = null;

    private int $quantity = 0;
    private bool $subtract = false;
    private float $price = 0.0;
    private string $pricePrefix = '';
    private int $points = 0;
    private string $pointsPrefix = '';
    private float $weight = 0.0;
    private string $weightPrefix = '';

    public function getProductOption(): ?ProductOption
    {
        return $this->productOption;
    }

    public function setProductOption(?ProductOption $productOption): self
    {
        $this->productOption = $productOption;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getOption(): ?Option
    {
        return $this->option;
    }

    public function setOption(?Option $option): self
    {
        $this->option = $option;
        return $this;
    }

    public function getOptionValue(): ?OptionValue
    {
        return $this->optionValue;
    }

    public function setOptionValue(?OptionValue $optionValue): self
    {
        $this->optionValue = $optionValue;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getSubtract(): bool
    {
        return $this->subtract;
    }

    public function setSubtract(bool $subtract): self
    {
        $this->subtract = $subtract;
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

    public function getPricePrefix(): string
    {
        return $this->pricePrefix;
    }

    public function setPricePrefix(string $pricePrefix): self
    {
        $this->pricePrefix = $pricePrefix;
        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
        return $this;
    }

    public function getPointsPrefix(): string
    {
        return $this->pointsPrefix;
    }

    public function setPointsPrefix(string $pointsPrefix): self
    {
        $this->pointsPrefix = $pointsPrefix;
        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getWeightPrefix(): string
    {
        return $this->weightPrefix;
    }

    public function setWeightPrefix(string $weightPrefix): self
    {
        $this->weightPrefix = $weightPrefix;
        return $this;
    }
}