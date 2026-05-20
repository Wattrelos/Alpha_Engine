<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductOptionValue - Detalhamento das variantes de produto (estoque e acréscimos).
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Impacto Financeiro: Gestão de prefixos (+/-) e valores de preço/peso para cálculo dinâmico no carrinho.
 * - Integridade de Estoque: Campo quantity e subtract tipados para garantir precisão na baixa de materiais pós-venda.
 * - Mapeamento Complexo: Atributos ManyToOne para ligar a variante ao produto pai e à definição da opção original (OptionValue).
 */
class ProductOptionValue extends BaseEntity
{
    private int $productOptionId = 0;
    private int $productId = 0;
    private int $optionId = 0;
    private int $optionValueId = 0;
    private int $quantity = 0;
    private bool $subtract = true;
    private float $price = 0.0000;
    private string $pricePrefix = '+';
    private int $points = 0;
    private string $pointsPrefix = '+';
    private float $weight = 0.00;
    private string $weightPrefix = '+';

    #[ManyToOne(targetEntity: ProductOption::class, foreignKey: 'productOptionId')]
    private ?ProductOption $productOption = null;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: OptionValue::class, foreignKey: 'optionValueId')]
    private ?OptionValue $optionValue = null;

    public function getProductOptionId(): int { return $this->productOptionId; }
    public function setProductOptionId(int $value): self { $this->productOptionId = $value; return $this; }

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $value): self { $this->productId = $value; return $this; }

    public function getOptionId(): int { return $this->optionId; }
    public function setOptionId(int $value): self { $this->optionId = $value; return $this; }

    public function getOptionValueId(): int { return $this->optionValueId; }
    public function setOptionValueId(int $value): self { $this->optionValueId = $value; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $value): self { $this->quantity = $value; return $this; }

    public function getSubtract(): bool { return $this->subtract; }
    public function setSubtract(bool|int $value): self { $this->subtract = (bool)$value; return $this; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $value): self { $this->price = $value; return $this; }

    public function getPricePrefix(): string { return $this->pricePrefix; }
    public function setPricePrefix(string $value): self { $this->pricePrefix = $value; return $this; }

    public function getPoints(): int { return $this->points; }
    public function setPoints(int $value): self { $this->points = $value; return $this; }

    public function getPointsPrefix(): string { return $this->pointsPrefix; }
    public function setPointsPrefix(string $value): self { $this->pointsPrefix = $value; return $this; }

    public function getWeight(): float { return $this->weight; }
    public function setWeight(float $value): self { $this->weight = $value; return $this; }

    public function getWeightPrefix(): string { return $this->weightPrefix; }
    public function setWeightPrefix(string $value): self { $this->weightPrefix = $value; return $this; }

    public function getProductOption(): ?ProductOption { return $this->productOption; }
    public function setProductOption(?ProductOption $option): self { $this->productOption = $option; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }

    public function getOptionValue(): ?OptionValue { return $this->optionValue; }
    public function setOptionValue(?OptionValue $value): self { $this->optionValue = $value; return $this; }
}