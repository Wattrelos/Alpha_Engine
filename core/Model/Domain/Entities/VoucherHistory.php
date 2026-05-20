<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade VoucherHistory - Rastreia o consumo de saldo de um voucher.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Auditoria de Saldo: Registra cada utilização parcial ou total do crédito.
 * - Tipagem PHP 8.4: Uso de float para valores e string para datas.
 * - Relacionamentos: #[ManyToOne] vinculando ao Voucher e ao Pedido de resgate.
 */
class VoucherHistory extends BaseEntity
{
    private int $voucherId = 0;
    private int $orderId = 0;
    private float $amount = 0.0;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Voucher::class, foreignKey: 'voucherId')]
    private ?Voucher $voucher = null;

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    public function getVoucherId(): int { return $this->voucherId; }
    public function setVoucherId(int $id): self { $this->voucherId = $id; return $this; }

    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $id): self { $this->orderId = $id; return $this; }

    public function getAmount(): float { return $this->amount; }
    public function setAmount(float $value): self { $this->amount = $value; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $value): self { $this->dateAdded = $value; return $this; }

    public function getVoucher(): ?Voucher
    {
        return $this->voucher;
    }
    public function setVoucher(?Voucher $voucher): self { $this->voucher = $voucher; return $this; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { $this->order = $order; return $this; }
}