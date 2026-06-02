<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Customer - Representa o cliente no domínio Alpha Engine.
 */
class Customer extends BaseEntity
{
    private int    $customerGroupId = 0;
    private int    $storeId = 0;
    private int    $languageId = 0;
    private string $firstname = '';
    private string $lastname = '';
    private string $email = '';
    private string $telephone = '';
    private string $password = '';
    private string $customField = '';
    private bool   $newsletter = false;
    private string $ip = '';
    private bool   $status = true;
    private bool   $safe = false;
    private bool   $commenter = false;
    private string $token = '';
    private string $code = '';
    private string $dateAdded = '';
    private string $cpfCnpj = '';     // Atributo personalizado
    private string $persontype = '';  // Atributo personalizado
    private int    $addressId = 0;    // ID do endereço padrão

    // Associações Muitos-para-Um

    #[ManyToOne(targetEntity: CustomerGroup::class, foreignKey: 'customerGroupId')]
    private ?CustomerGroup $customerGroup = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    // Associações Um-para-Muitos

    #[OneToMany(targetEntity: Address::class, foreignKey: 'customerId')]
    private array $addresses = [];

    #[OneToMany(targetEntity: Order::class, foreignKey: 'customerId')]
    private array $orders = [];

    public function __construct() {
        parent::__construct();
        $this->addresses = [];
        $this->orders = [];
        $this->dateAdded = date('Y-m-d H:i:s');
    }

    // Getters e Setters

    public function getCustomerGroupId(): int {
        return $this->customerGroupId;
    }

    public function setCustomerGroupId(int $customerGroupId): self {
        $this->customerGroupId = $customerGroupId;
        return $this;
    }

    public function getStoreId(): int {
        return $this->storeId;
    }

    public function setStoreId(int $storeId): self {
        $this->storeId = $storeId;
        return $this;
    }

    public function getLanguageId(): int {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): self {
        $this->languageId = $languageId;
        return $this;
    }

    public function getAddressId(): int {
        return $this->addressId;
    }

    public function setAddressId(int $addressId): self {
        $this->addressId = $addressId;
        return $this;
    }

    public function getFirstname(): string {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): string {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self {
        $this->lastname = $lastname;
        return $this;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): string {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self {
        $this->telephone = $telephone;
        return $this;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;
        return $this;
    }

    public function getCustomField(): string {
        return $this->customField;
    }

    public function getCustomFieldArray(): array {
        return json_decode($this->customField, true) ?: [];
    }

    public function setCustomField(string|array $customField): self {
        $this->customField = is_array($customField) ? json_encode($customField) : $customField;
        return $this;
    }

    public function isNewsletter(): bool {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): self {
        $this->newsletter = $newsletter;
        return $this;
    }

    public function getIp(): string {
        return $this->ip;
    }

    public function setIp(string $ip): self {
        $this->ip = $ip;
        return $this;
    }

    public function isStatus(): bool {
        return $this->status;
    }

    public function setStatus(bool $status): self {
        $this->status = $status;
        return $this;
    }

    public function isSafe(): bool {
        return $this->safe;
    }

    public function setSafe(bool $safe): self {
        $this->safe = $safe;
        return $this;
    }

    public function isCommenter(): bool {
        return $this->commenter;
    }

    public function setCommenter(bool|int $commenter): self {
        $this->commenter = (bool)$commenter;
        return $this;
    }

    public function getToken(): string {
        return $this->token;
    }

    public function setToken(string $token): self {
        $this->token = $token;
        return $this;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function setCode(string $code): self {
        $this->code = $code;
        return $this;
    }

    public function getCustomerGroup(): ?CustomerGroup {
        return $this->customerGroup;
    }

    public function setCustomerGroup(?CustomerGroup $customerGroup): self {
        $this->customerGroup = $customerGroup;
        return $this;
    }

    public function getNewsletter(): bool {
        return $this->newsletter;
    }

    public function getStatus(): bool {
        return $this->status;
    }

    public function getSafe(): bool {
        return $this->safe;
    }

    public function getDateAdded(): string {
        return $this->dateAdded;
    }

    public function getCpfCnpj(): string {
        return $this->cpfCnpj;
    }

    public function setCpfCnpj(string $cpfCnpj): self {
        $this->cpfCnpj = $cpfCnpj;
        return $this;
    }

    public function getPersontype(): string {
        return $this->persontype;
    }

    public function setPersontype(string $persontype): self {
        $this->persontype = $persontype;
        return $this;
    }


    public function setDateAdded(string $dateAdded): self {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getAddresses(): array {
        return $this->addresses;
    }

    public function setAddresses(array $addresses): self {
        $this->addresses = $addresses;
        return $this;
    }

    public function getOrders(): array {
        return $this->orders;
    }

    public function setOrders(array $orders): self {
        $this->orders = $orders;
        return $this;
    }
}