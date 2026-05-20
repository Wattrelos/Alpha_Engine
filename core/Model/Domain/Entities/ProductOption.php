<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade ProductOption - Cabeçalho de variações específicas do produto.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Controle de Obrigatoriedade: Propriedade booleana estrita para 'required', facilitando a validação no carrinho.
 * - Flexibilidade de Valor: Propriedade 'value' tipada como string para suportar opções de texto, data ou hora diretamente.
 * - Cascata de Variações: #[OneToMany] para carregar todos os 'ProductOptionValue' vinculados de forma automática.
 */
class ProductOption extends BaseEntity
{
    private int $productId = 0;
    private int $optionId = 0;
    private string $value = '';
    private bool $required = true;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Option::class, foreignKey: 'optionId')]
    private ?Option $option = null;

    /** @var ProductOptionValue[] */
    #[OneToMany(targetEntity: ProductOptionValue::class, mappedBy: "productOption", foreignKey: "productOptionId")]
    private array $productOptionValues = [];

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $value): self { $this->productId = $value; return $this; }

    public function getOptionId(): int { return $this->optionId; }
    public function setOptionId(int $value): self { $this->optionId = $value; return $this; }

    public function getValue(): string { return $this->value; }
    public function setValue(string $value): self { $this->value = $value; return $this; }

    public function getRequired(): bool { return $this->required; }
    public function setRequired(bool|int $value): self { $this->required = (bool)$value; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }

    public function getOption(): ?Option { return $this->option; }
    public function setOption(?Option $option): self { $this->option = $option; return $this; }

    public function getProductOptionValues(): array { return $this->productOptionValues; }
    public function setProductOptionValues(array $values): self { $this->productOptionValues = $values; return $this; }
}