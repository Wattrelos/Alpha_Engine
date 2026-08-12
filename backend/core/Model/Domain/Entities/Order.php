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
    private string $telephone = '';
    private string $paymentMethod = '';
    private string $shippingMethod = '';
    private float  $total = 0.0000;
    private int    $orderStatusId = 0;
    private int    $subscriptionId = 0;
    private int    $invoiceNo = 0;
    private string $invoicePrefix = '';
    private string $transactionId = '';
    private string $storeName = '';
    private string $storeUrl = '';
    private int    $customerGroupId = 0;
    private int    $paymentAddressId = 0;
    private string $paymentFirstname = '';
    private string $paymentLastname = '';
    private string $paymentCompany = '';
    private string $paymentStreet = '';
    private int    $paymentNumber = 0;
    private string $paymentComplement = '';
    private string $paymentDistrict = '';
    private string $paymentCity = '';
    private string $paymentPostcode = '';
    private string $paymentCountry = '';
    private int    $paymentCountryId = 0;
    private string $paymentZone = '';
    private int    $paymentZoneId = 0;
    private string $paymentAddressFormat = '';
    private int    $shippingAddressId = 0;
    private string $shippingFirstname = '';
    private string $shippingLastname = '';
    private string $shippingCompany = '';
    private string $shippingStreet = '';
    private int    $shippingNumber = 0;
    private string $shippingComplement = '';
    private string $shippingDistrict = '';
    private string $shippingCity = '';
    private string $shippingPostcode = '';
    private string $shippingCountry = '';
    private int    $shippingCountryId = 0;
    private string $shippingZone = '';
    private int    $shippingZoneId = 0;
    private string $shippingAddressFormat = '';
    private string $comment = '';
    private int    $affiliateId = 0;
    private float  $commission = 0.0000;
    private int    $marketingId = 0;
    private string $tracking = '';
    private int    $languageId = 0;
    private string $languageCode = '';
    private int $currencyId = 0;
    private string $ip = '';
    private string $forwardedIp = '';
    private string $userAgent = '';
    private string $acceptLanguage = '';
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
    public function getStoreId(): int
    {
        return $this->storeId;
    }
    public function setStoreId(int $id): self
    {
        $this->storeId = $id;
        return $this;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }
    public function setCustomerId(int $id): self
    {
        $this->customerId = $id;
        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }
    public function setFirstname(string $name): self
    {
        $this->firstname = $name;
        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }
    public function setLastname(string $name): self
    {
        $this->lastname = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }
    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }
    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getShippingMethod(): string
    {
        return $this->shippingMethod;
    }
    public function setShippingMethod(string $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    public function getTotal(): float
    {
        return $this->total;
    }
    public function setTotal(float $total): self
    {
        $this->total = $total;
        return $this;
    }

    public function getOrderStatusId(): int
    {
        return $this->orderStatusId;
    }
    public function setOrderStatusId(int $id): self
    {
        $this->orderStatusId = $id;
        return $this;
    }

    public function getSubscriptionId(): int
    {
        return $this->subscriptionId;
    }
    public function setSubscriptionId(int $subscriptionId): self
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    public function getInvoiceNo(): int
    {
        return $this->invoiceNo;
    }
    public function setInvoiceNo(int $invoiceNo): self
    {
        $this->invoiceNo = $invoiceNo;
        return $this;
    }

    public function getInvoicePrefix(): string
    {
        return $this->invoicePrefix;
    }
    public function setInvoicePrefix(string $invoicePrefix): self
    {
        $this->invoicePrefix = $invoicePrefix;
        return $this;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }
    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function getStoreName(): string
    {
        return $this->storeName;
    }
    public function setStoreName(string $storeName): self
    {
        $this->storeName = $storeName;
        return $this;
    }

    public function getStoreUrl(): string
    {
        return $this->storeUrl;
    }
    public function setStoreUrl(string $storeUrl): self
    {
        $this->storeUrl = $storeUrl;
        return $this;
    }

    public function getCustomerGroupId(): int
    {
        return $this->customerGroupId;
    }
    public function setCustomerGroupId(int $customerGroupId): self
    {
        $this->customerGroupId = $customerGroupId;
        return $this;
    }

    public function getPaymentAddressId(): int
    {
        return $this->paymentAddressId;
    }
    public function setPaymentAddressId(int $paymentAddressId): self
    {
        $this->paymentAddressId = $paymentAddressId;
        return $this;
    }

    public function getPaymentFirstname(): string
    {
        return $this->paymentFirstname;
    }
    public function setPaymentFirstname(string $paymentFirstname): self
    {
        $this->paymentFirstname = $paymentFirstname;
        return $this;
    }

    public function getPaymentLastname(): string
    {
        return $this->paymentLastname;
    }
    public function setPaymentLastname(string $paymentLastname): self
    {
        $this->paymentLastname = $paymentLastname;
        return $this;
    }

    public function getPaymentCompany(): string
    {
        return $this->paymentCompany;
    }
    public function setPaymentCompany(string $paymentCompany): self
    {
        $this->paymentCompany = $paymentCompany;
        return $this;
    }

    public function getPaymentStreet(): string
    {
        return $this->paymentStreet;
    }
    public function setPaymentStreet(string $paymentStreet): self
    {
        $this->paymentStreet = $paymentStreet;
        return $this;
    }

    public function getPaymentNumber(): int
    {
        return $this->paymentNumber;
    }
    public function setPaymentNumber(int $paymentNumber): self
    {
        $this->paymentNumber = $paymentNumber;
        return $this;
    }

    public function getPaymentComplement(): string
    {
        return $this->paymentComplement;
    }
    public function setPaymentComplement(string $paymentComplement): self
    {
        $this->paymentComplement = $paymentComplement;
        return $this;
    }

    public function getPaymentDistrict(): string
    {
        return $this->paymentDistrict;
    }
    public function setPaymentDistrict(string $paymentDistrict): self
    {
        $this->paymentDistrict = $paymentDistrict;
        return $this;
    }

    public function getPaymentCity(): string
    {
        return $this->paymentCity;
    }
    public function setPaymentCity(string $paymentCity): self
    {
        $this->paymentCity = $paymentCity;
        return $this;
    }

    public function getPaymentPostcode(): string
    {
        return $this->paymentPostcode;
    }
    public function setPaymentPostcode(string $paymentPostcode): self
    {
        $this->paymentPostcode = $paymentPostcode;
        return $this;
    }

    public function getPaymentCountry(): string
    {
        return $this->paymentCountry;
    }
    public function setPaymentCountry(string $paymentCountry): self
    {
        $this->paymentCountry = $paymentCountry;
        return $this;
    }

    public function getPaymentCountryId(): int
    {
        return $this->paymentCountryId;
    }
    public function setPaymentCountryId(int $paymentCountryId): self
    {
        $this->paymentCountryId = $paymentCountryId;
        return $this;
    }

    public function getPaymentZone(): string
    {
        return $this->paymentZone;
    }
    public function setPaymentZone(string $paymentZone): self
    {
        $this->paymentZone = $paymentZone;
        return $this;
    }

    public function getPaymentZoneId(): int
    {
        return $this->paymentZoneId;
    }
    public function setPaymentZoneId(int $paymentZoneId): self
    {
        $this->paymentZoneId = $paymentZoneId;
        return $this;
    }

    public function getPaymentAddressFormat(): string
    {
        return $this->paymentAddressFormat;
    }
    public function setPaymentAddressFormat(string $paymentAddressFormat): self
    {
        $this->paymentAddressFormat = $paymentAddressFormat;
        return $this;
    }

    public function getShippingAddressId(): int
    {
        return $this->shippingAddressId;
    }
    public function setShippingAddressId(int $shippingAddressId): self
    {
        $this->shippingAddressId = $shippingAddressId;
        return $this;
    }

    public function getShippingFirstname(): string
    {
        return $this->shippingFirstname;
    }
    public function setShippingFirstname(string $shippingFirstname): self
    {
        $this->shippingFirstname = $shippingFirstname;
        return $this;
    }

    public function getShippingLastname(): string
    {
        return $this->shippingLastname;
    }
    public function setShippingLastname(string $shippingLastname): self
    {
        $this->shippingLastname = $shippingLastname;
        return $this;
    }

    public function getShippingCompany(): string
    {
        return $this->shippingCompany;
    }
    public function setShippingCompany(string $shippingCompany): self
    {
        $this->shippingCompany = $shippingCompany;
        return $this;
    }

    public function getShippingStreet(): string
    {
        return $this->shippingStreet;
    }
    public function setShippingStreet(string $shippingStreet): self
    {
        $this->shippingStreet = $shippingStreet;
        return $this;
    }

    public function getShippingNumber(): int
    {
        return $this->shippingNumber;
    }
    public function setShippingNumber(int $shippingNumber): self
    {
        $this->shippingNumber = $shippingNumber;
        return $this;
    }

    public function getShippingComplement(): string
    {
        return $this->shippingComplement;
    }
    public function setShippingComplement(string $shippingComplement): self
    {
        $this->shippingComplement = $shippingComplement;
        return $this;
    }

    public function getShippingDistrict(): string
    {
        return $this->shippingDistrict;
    }
    public function setShippingDistrict(string $shippingDistrict): self
    {
        $this->shippingDistrict = $shippingDistrict;
        return $this;
    }

    public function getShippingCity(): string
    {
        return $this->shippingCity;
    }
    public function setShippingCity(string $shippingCity): self
    {
        $this->shippingCity = $shippingCity;
        return $this;
    }

    public function getShippingPostcode(): string
    {
        return $this->shippingPostcode;
    }
    public function setShippingPostcode(string $shippingPostcode): self
    {
        $this->shippingPostcode = $shippingPostcode;
        return $this;
    }

    public function getShippingCountry(): string
    {
        return $this->shippingCountry;
    }
    public function setShippingCountry(string $shippingCountry): self
    {
        $this->shippingCountry = $shippingCountry;
        return $this;
    }

    public function getShippingCountryId(): int
    {
        return $this->shippingCountryId;
    }
    public function setShippingCountryId(int $shippingCountryId): self
    {
        $this->shippingCountryId = $shippingCountryId;
        return $this;
    }

    public function getShippingZone(): string
    {
        return $this->shippingZone;
    }
    public function setShippingZone(string $shippingZone): self
    {
        $this->shippingZone = $shippingZone;
        return $this;
    }

    public function getShippingZoneId(): int
    {
        return $this->shippingZoneId;
    }
    public function setShippingZoneId(int $shippingZoneId): self
    {
        $this->shippingZoneId = $shippingZoneId;
        return $this;
    }

    public function getShippingAddressFormat(): string
    {
        return $this->shippingAddressFormat;
    }
    public function setShippingAddressFormat(string $shippingAddressFormat): self
    {
        $this->shippingAddressFormat = $shippingAddressFormat;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getAffiliateId(): int
    {
        return $this->affiliateId;
    }
    public function setAffiliateId(int $affiliateId): self
    {
        $this->affiliateId = $affiliateId;
        return $this;
    }

    public function getCommission(): float
    {
        return $this->commission;
    }
    public function setCommission(float $commission): self
    {
        $this->commission = $commission;
        return $this;
    }

    public function getMarketingId(): int
    {
        return $this->marketingId;
    }
    public function setMarketingId(int $marketingId): self
    {
        $this->marketingId = $marketingId;
        return $this;
    }

    public function getTracking(): string
    {
        return $this->tracking;
    }
    public function setTracking(string $tracking): self
    {
        $this->tracking = $tracking;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }
    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;
        return $this;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }
    public function setLanguageCode(string $languageCode): self
    {
        $this->languageCode = $languageCode;
        return $this;
    }

    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }
    public function setCurrencyId(int $currencyId): self
    {
        $this->currencyId = $currencyId;
        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }
    public function setIp(string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    public function getForwardedIp(): string
    {
        return $this->forwardedIp;
    }
    public function setForwardedIp(string $forwardedIp): self
    {
        $this->forwardedIp = $forwardedIp;
        return $this;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }
    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getAcceptLanguage(): string
    {
        return $this->acceptLanguage;
    }
    public function setAcceptLanguage(string $acceptLanguage): self
    {
        $this->acceptLanguage = $acceptLanguage;
        return $this;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
    public function setCurrencyCode(string $code): self
    {
        $this->currencyCode = $code;
        return $this;
    }

    public function getCurrencyValue(): float
    {
        return $this->currencyValue;
    }
    public function setCurrencyValue(float $value): self
    {
        $this->currencyValue = $value;
        return $this;
    }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }
    public function setDateAdded(string $date): self
    {
        $this->dateAdded = $date;
        return $this;
    }

    public function getDateModified(): string
    {
        return $this->dateModified;
    }
    public function setDateModified(string $dateModified): self
    {
        $this->dateModified = $dateModified;
        return $this;
    }

    /** @return OrderProduct[] */
    public function getProducts(): array
    {
        return $this->products;
    }
    public function setProducts(array $products): self
    {
        $this->products = $products;
        return $this;
    }

    /** @return OrderTotal[] */
    public function getTotals(): array
    {
        return $this->totals;
    }
    public function setTotals(array $totals): self
    {
        $this->totals = $totals;
        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }
    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function getOrderStatus(): ?OrderStatus
    {
        return $this->orderStatus;
    }
    public function setOrderStatus(?OrderStatus $status): self
    {
        $this->orderStatus = $status;
        return $this;
    }
}
