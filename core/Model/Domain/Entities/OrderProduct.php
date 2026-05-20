<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade OrderProduct - Snapshot dos produtos adquiridos.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Persistência Histórica: Armazena nome, modelo e preço unitário no ato da compra, protegendo o pedido contra mudanças de catálogo.
 * - Precisão de Cálculo: price, total e tax tipados como float para auditoria de totais.
 * - Injeção Relacional: #[ManyToOne] para vincular ao Pedido e ao Produto original (permitindo recompras).
 * - Cascata de Variações: #[OneToMany] para carregar as opções (OrderOption) selecionadas pelo cliente.
 */
class OrderProduct extends BaseEntity
{
    private int $orderId = 0;
    private int $productId = 0;
    private string $name = '';
    private string $model = '';
    private int $quantity = 0;
    private float $price = 0.0000;
    private float $total = 0.0000;
    private float $tax = 0.0000;

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    /**
     * @var OrderOption[]
     */
    #[OneToMany(targetEntity: OrderOption::class, mappedBy: "orderProduct", foreignKey: "orderProductId")]
    private array $options = [];

    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $id): self { $this->orderId = $id; return $this; }

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $id): self { $this->productId = $id; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $qty): self { $this->quantity = $qty; return $this; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): self { $this->price = $price; return $this; }

    public function getTotal(): float { return $this->total; }
    public function setTotal(float $total): self { $this->total = $total; return $this; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { $this->order = $order; return $this; }

    public function getOptions(): array { return $this->options; }
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }
}