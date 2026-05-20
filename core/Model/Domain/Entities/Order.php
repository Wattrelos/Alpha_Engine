<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Order - O registro definitivo de uma transação comercial.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Imutabilidade de Dados: Projetada para armazenar snapshots (preço, endereços) no momento da compra, garantindo que alterações futuras em produtos ou clientes não corrompam o histórico financeiro.
 * - Orquestração Complexa: Atributos #[OneToMany] para produtos, totais e históricos, permitindo ao DAO gerenciar transações atômicas de escrita.
 * - Precisão Financeira: 'total' e campos de moeda tipados como float para evitar disparidades em gateways de pagamento.
 * - Localização Snapshot: Mantém currencyCode e currencyValue para que o pedido possa ser visualizado com o câmbio da data da venda.
 */
class Order extends BaseEntity
{
    private int $storeId = 0;
    private int $customerId = 0;
    private string $firstname = '';
    private string $lastname = '';
    private string $email = '';
    private string $paymentMethod = '';
    private string $shippingMethod = '';
    private float $total = 0.0000;
    private int $orderStatusId = 0;
    private string $currencyCode = 'BRL';
    private float $currencyValue = 1.00000000;
    private string $dateAdded = '';
    private string $dateModified = '';

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: OrderStatus::class, foreignKey: 'orderStatusId')]
    private ?OrderStatus $orderStatus = null;

    /** @var OrderProduct[] */
    #[OneToMany(targetEntity: OrderProduct::class, mappedBy: "order", foreignKey: "orderId")]
    private array $products = [];

    /** @var OrderTotal[] */
    #[OneToMany(targetEntity: OrderTotal::class, mappedBy: "order", foreignKey: "orderId")]
    private array $totals = [];

    /** @var OrderHistory[] */
    #[OneToMany(targetEntity: OrderHistory::class, mappedBy: "order", foreignKey: "orderId")]
    private array $histories = [];

    // Getters e Setters Fluídos
    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $id): self { $this->storeId = $id; return $this; }

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $id): self { $this->customerId = $id; return $this; }

    public function getFirstname(): string { return $this->firstname; }
    public function setFirstname(string $name): self { $this->firstname = $name; return $this; }

    public function getLastname(): string { return $this->lastname; }
    public function setLastname(string $name): self { $this->lastname = $name; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getTotal(): float { return $this->total; }
    public function setTotal(float $total): self { $this->total = $total; return $this; }

    public function getOrderStatusId(): int { return $this->orderStatusId; }
    public function setOrderStatusId(int $id): self { $this->orderStatusId = $id; return $this; }

    public function getCurrencyCode(): string { return $this->currencyCode; }
    public function setCurrencyCode(string $code): self { $this->currencyCode = $code; return $this; }

    public function getCurrencyValue(): float { return $this->currencyValue; }
    public function setCurrencyValue(float $value): self { $this->currencyValue = $value; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    /** @return OrderProduct[] */
    public function getProducts(): array { return $this->products; }
    public function setProducts(array $products): self { $this->products = $products; return $this; }

    /** @return OrderTotal[] */
    public function getTotals(): array { return $this->totals; }
    public function setTotals(array $totals): self { $this->totals = $totals; return $this; }

    public function getStore(): ?Store { return $this->store; }
    public function setStore(?Store $store): self { $this->store = $store; return $this; }

    public function getOrderStatus(): ?OrderStatus { return $this->orderStatus; }
    public function setOrderStatus(?OrderStatus $status): self { $this->orderStatus = $status; return $this; }
}