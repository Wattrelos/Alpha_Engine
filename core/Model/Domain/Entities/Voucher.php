<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Voucher - Representa um cartão de presente/vale-compra.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Segurança de Crédito: Valor (amount) tipado como float para cálculos financeiros precisos.
 * - Rastreabilidade: Vinculação com Order via ManyToOne para auditoria de origem.
 * - Estética: Relacionamento com VoucherTheme para definição visual do presente.
 * - Integridade: Status booleano para controle de ativação/uso.
 */
class Voucher extends BaseEntity
{
    private int $orderId = 0;
    private string $code = '';
    private string $fromName = '';
    private string $fromEmail = '';
    private string $toName = '';
    private string $toEmail = '';
    private int $voucherThemeId = 0;
    private string $message = '';
    private float $amount = 0.0;
    private bool $status = false;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    #[ManyToOne(targetEntity: VoucherTheme::class, foreignKey: 'voucherThemeId')]
    private ?VoucherTheme $voucherTheme = null;

    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $value): self { $this->orderId = $value; return $this; }

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
    public function setVoucherThemeId(int $value): self { $this->voucherThemeId = $value; return $this; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $value): self { $this->message = $value; return $this; }

    public function getAmount(): float { return $this->amount; }
    public function setAmount(float $value): self { $this->amount = $value; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $value): self { $this->status = $value; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $value): self { $this->dateAdded = $value; return $this; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { $this->order = $order; return $this; }

    public function getVoucherTheme(): ?VoucherTheme { return $this->voucherTheme; }
    public function setVoucherTheme(?VoucherTheme $theme): self { $this->voucherTheme = $theme; return $this; }
}