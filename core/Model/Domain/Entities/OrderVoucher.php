<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade OrderVoucher - Detalhes de um voucher adquirido dentro de um pedido.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Snapshot de Transação: Registra os dados do presente no momento da compra.
 * - Tipagem PHP 8.4: Uso de float para o valor do voucher e int para chaves.
 * - Relacionamentos: #[ManyToOne] vinculando ao pedido original e ao tema visual.
 */
class OrderVoucher extends BaseEntity
{
    private int $orderId = 0;
    private int $voucherId = 0;
    private string $description = '';
    private string $code = '';
    private string $fromName = '';
    private string $fromEmail = '';
    private string $toName = '';
    private string $toEmail = '';
    private int $voucherThemeId = 0;
    private string $message = '';
    private float $amount = 0.0;

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    #[ManyToOne(targetEntity: VoucherTheme::class, foreignKey: 'voucherThemeId')]
    private ?VoucherTheme $voucherTheme = null;

    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $id): self { $this->orderId = $id; return $this; }

    public function getVoucherId(): int { return $this->voucherId; }
    public function setVoucherId(int $id): self { $this->voucherId = $id; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $value): self { $this->description = $value; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $value): self { $this->code = $value; return $this; }

    public function getFromName(): string { return $this->fromName; }
    public function setFromName(string $value): self { $this->fromName = $value; return $this; }

    public function getFromEmail(): string { return $this->fromEmail; }
    public function setFromEmail(string $value): self { $this->fromEmail = $value; return $this; }

    public function getToName(): string { return $this->toName; }
    public function setToName(string $value): self { $this->toName = $value; return $this; }

    public function getToEmail(): string { return $this->toEmail; }
    public function setToEmail(string $value): self { $this->toEmail = $value; return $this; }

    public function getVoucherThemeId(): int { return $this->voucherThemeId; }
    public function setVoucherThemeId(int $id): self { $this->voucherThemeId = $id; return $this; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $value): self { $this->message = $value; return $this; }

    public function getAmount(): float { return $this->amount; }
    public function setAmount(float $value): self { $this->amount = $value; return $this; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { $this->order = $order; return $this; }

    public function getVoucherTheme(): ?VoucherTheme { return $this->voucherTheme; }
    public function setVoucherTheme(?VoucherTheme $theme): self { $this->voucherTheme = $theme; return $this; }
}