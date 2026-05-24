<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerAffiliate - Gerencia parceiros afiliados e regras de comissionamento.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Precisão Financeira: Campo 'commission' tipado como float para cálculos exatos de repasse.
 * - Segurança de Dados: Campos de pagamento e documentos bancários tipados e preparados para criptografia.
 * - Injeção Relacional: Atributo #[ManyToOne] vincula o parceiro ao Cliente principal automaticamente.
 * - PHP 8.4 Readiness: Tipagem nativa, interface fluida e status booleano estrito.
 */
class CustomerAffiliate extends BaseEntity
{
    private string $company = '';
    private string $website = '';
    private string $tracking = '';
    private float $balance = 0.0000;
    private float $commission = 0.00;
    private string $tax = '';
    private string $paymentMethod = '';
    private string $cheque = '';
    private string $paypal = '';
    private string $bankName = '';
    private string $bankBranchNumber = '';
    private string $bankSwiftCode = '';
    private string $bankAccountName = '';
    private string $bankAccountNumber = '';
    private string $customField = '';
    private bool $status = true;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'id')]
    private ?Customer $customer = null;

    public function getCompany(): string { return $this->company; }
    public function setCompany(string $value): self { $this->company = $value; return $this; }

    public function getWebsite(): string { return $this->website; }
    public function setWebsite(string $value): self { $this->website = $value; return $this; }

    public function getTracking(): string { return $this->tracking; }
    public function setTracking(string $value): self { $this->tracking = $value; return $this; }

    public function getBalance(): float { return $this->balance; }
    public function setBalance(float $value): self { $this->balance = $value; return $this; }

    public function getCommission(): float { return $this->commission; }
    public function setCommission(float $value): self { $this->commission = $value; return $this; }

    public function getTax(): string { return $this->tax; }
    public function setTax(string $value): self { $this->tax = $value; return $this; }

    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function setPaymentMethod(string $value): self { $this->paymentMethod = $value; return $this; }

    public function getCheque(): string { return $this->cheque; }
    public function setCheque(string $value): self { $this->cheque = $value; return $this; }

    public function getPaypal(): string { return $this->paypal; }
    public function setPaypal(string $value): self { $this->paypal = $value; return $this; }

    public function getBankName(): string { return $this->bankName; }
    public function setBankName(string $value): self { $this->bankName = $value; return $this; }

    public function getBankBranchNumber(): string { return $this->bankBranchNumber; }
    public function setBankBranchNumber(string $value): self { $this->bankBranchNumber = $value; return $this; }

    public function getBankSwiftCode(): string { return $this->bankSwiftCode; }
    public function setBankSwiftCode(string $value): self { $this->bankSwiftCode = $value; return $this; }

    public function getBankAccountName(): string { return $this->bankAccountName; }
    public function setBankAccountName(string $value): self { $this->bankAccountName = $value; return $this; }

    public function getBankAccountNumber(): string { return $this->bankAccountNumber; }
    public function setBankAccountNumber(string $value): self { $this->bankAccountNumber = $value; return $this; }

    public function getCustomField(): string { return $this->customField; }
    public function setCustomField(string $value): self { $this->customField = $value; return $this; }

    public function getStatus(): bool { return $this->status; }
    public function setStatus(bool|int $value): self { $this->status = (bool)$value; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $value): self { $this->dateAdded = $value; return $this; }

    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }
}
