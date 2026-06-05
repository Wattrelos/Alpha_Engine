<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;
 use Alpha\Model\Domain\Entities\Customer\Customer;

/**
 * Entidade CouponHistory - Rastreia a utilização de cupons por pedido e cliente.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Auditoria de Desconto: Registra o valor exato (float) abatido no momento da transação.
 * - Rastreabilidade: Vinculação completa entre o cupom, o pedido gerado e o cliente.
 * - Tipagem Estrita: Uso de tipos nativos para garantir cálculos de auditoria precisos.
 */
class CouponHistory extends BaseEntity
{
    private int $couponId = 0;
    private int $orderId = 0;
    private int $customerId = 0;
    private float $amount = 0.0000;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Coupon::class, foreignKey: 'couponId')]
    private ?Coupon $coupon = null;

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    public function getCouponId(): int { return $this->couponId; }
    public function setCouponId(int $id): self { $this->couponId = $id; return $this; }

    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $id): self { $this->orderId = $id; return $this; }

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $id): self { $this->customerId = $id; return $this; }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $value): self
    {
        $this->amount = $value;
        return $this;
    }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $value): self
    {
        $this->dateAdded = $value;
        return $this;
    }

    public function getCoupon(): ?Coupon { return $this->coupon; }
    public function setCoupon(?Coupon $coupon): self { $this->coupon = $coupon; return $this; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { $this->order = $order; return $this; }

    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }
}
