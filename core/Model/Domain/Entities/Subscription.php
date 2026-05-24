<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Subscription - Gerencia contratos de assinaturas ativos de clientes.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Snapshot de Contrato: Armazena cópias de preços e ciclos no momento da venda (imutabilidade).
 * - Vinculação Tripartite: Relaciona Order, Customer e SubscriptionPlan via ManyToOne.
 * - Tipagem Estrita: Garantia de que cálculos de recorrência usem tipos numéricos corretos.
 * - Auditoria: Campos de data e comentários integrados para rastreabilidade de suporte.
 */
class Subscription extends BaseEntity
{
    private int $storeId = 0;
    private int $paymentAddressId = 0;
    private string $paymentMethod = '';
    private int $shippingAddressId = 0;
    private string $shippingMethod = '';
    private float $trialPrice = 0.0;
    private float $trialTax = 0.0;
    private string $trialFrequency = '';
    private int $trialCycle = 0;
    private int $trialDuration = 0;
    private int $trialRemaining = 0;
    private bool $trialStatus = false;
    private float $price = 0.0;
    private float $tax = 0.0;
    private string $frequency = '';
    private int $cycle = 0;
    private int $duration = 0;
    private int $remaining = 0;
    private string $dateNext = '';
    private string $language = '';
    private string $currency = '';
    private int $subscriptionStatusId = 0;
    private string $comment = '';
    private string $dateAdded = '';
    private string $dateModified = '';

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: SubscriptionPlan::class, foreignKey: 'subscriptionPlanId')]
    private ?SubscriptionPlan $subscriptionPlan = null;

    public function getOrderId(): int { return $this->order ? (int)$this->order->getId() : 0; }
    public function setOrderId(int $value): self 
    { 
        if (!$this->order) $this->order = new Order();
        $this->order->setId($value); 
        return $this; 
    }

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $value): self { $this->storeId = $value; return $this; }

    public function getPaymentAddressId(): int { return $this->paymentAddressId; }
    public function setPaymentAddressId(int $value): self { $this->paymentAddressId = $value; return $this; }

    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function setPaymentMethod(string $value): self { $this->paymentMethod = $value; return $this; }

    public function getShippingAddressId(): int { return $this->shippingAddressId; }
    public function setShippingAddressId(int $value): self { $this->shippingAddressId = $value; return $this; }

    public function getShippingMethod(): string { return $this->shippingMethod; }
    public function setShippingMethod(string $value): self { $this->shippingMethod = $value; return $this; }

    public function getCustomerId(): int { return $this->customer ? (int)$this->customer->getId() : 0; }
    public function setCustomerId(int $value): self 
    { 
        if (!$this->customer) $this->customer = new Customer();
        $this->customer->setId($value); 
        return $this; 
    }

    public function getSubscriptionPlanId(): int 
    { 
        return $this->subscriptionPlan ? (int)$this->subscriptionPlan->getId() : 0; 
    }
    
    public function setSubscriptionPlanId(int $value): self 
    { 
        if (!$this->subscriptionPlan) $this->subscriptionPlan = new SubscriptionPlan();
        $this->subscriptionPlan->setId($value); 
        return $this; 
    }

    public function getTrialPrice(): float { return $this->trialPrice; }
    public function setTrialPrice(float $value): self { $this->trialPrice = $value; return $this; }

    public function getTrialTax(): float { return $this->trialTax; }
    public function setTrialTax(float $value): self { $this->trialTax = $value; return $this; }

    public function getTrialFrequency(): string { return $this->trialFrequency; }
    public function setTrialFrequency(string $value): self { $this->trialFrequency = $value; return $this; }

    public function getTrialCycle(): int { return $this->trialCycle; }
    public function setTrialCycle(int $value): self { $this->trialCycle = $value; return $this; }

    public function getTrialDuration(): int { return $this->trialDuration; }
    public function setTrialDuration(int $value): self { $this->trialDuration = $value; return $this; }

    public function getTrialRemaining(): int { return $this->trialRemaining; }
    public function setTrialRemaining(int $value): self { $this->trialRemaining = $value; return $this; }

    public function isTrialStatus(): bool { return $this->trialStatus; }
    public function setTrialStatus(bool|int $value): self { $this->trialStatus = (bool)$value; return $this; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $value): self { $this->price = $value; return $this; }

    public function getTax(): float { return $this->tax; }
    public function setTax(float $value): self { $this->tax = $value; return $this; }

    public function getFrequency(): string { return $this->frequency; }
    public function setFrequency(string $value): self { $this->frequency = $value; return $this; }

    public function getCycle(): int { return $this->cycle; }
    public function setCycle(int $value): self { $this->cycle = $value; return $this; }

    public function getDuration(): int { return $this->duration; }
    public function setDuration(int $value): self { $this->duration = $value; return $this; }

    public function getRemaining(): int { return $this->remaining; }
    public function setRemaining(int $value): self { $this->remaining = $value; return $this; }

    public function getDateNext(): string { return $this->dateNext; }
    public function setDateNext(string $value): self { $this->dateNext = $value; return $this; }

    public function getLanguage(): string { return $this->language; }
    public function setLanguage(string $value): self { $this->language = $value; return $this; }

    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $value): self { $this->currency = $value; return $this; }

    public function getSubscriptionStatusId(): int { return $this->subscriptionStatusId; }
    public function setSubscriptionStatusId(int $value): self { $this->subscriptionStatusId = $value; return $this; }

    public function getComment(): string { return $this->comment; }
    public function setComment(string $value): self { $this->comment = $value; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $value): self { $this->dateAdded = $value; return $this; }

    public function getDateModified(): string { return $this->dateModified; }
    public function setDateModified(string $value): self { $this->dateModified = $value; return $this; }

    // Objetos Relacionados (Hidratação via DAO)

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { $this->order = $order; return $this; }

    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }

    public function getSubscriptionPlan(): ?SubscriptionPlan { return $this->subscriptionPlan; }
    public function setSubscriptionPlan(?SubscriptionPlan $plan): self { $this->subscriptionPlan = $plan; return $this; }
}