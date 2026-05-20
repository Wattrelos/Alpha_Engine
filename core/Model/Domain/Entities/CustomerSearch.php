<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerSearch - Histórico de termos de pesquisa utilizados pelos clientes.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Inteligência de Negócio: Rastreia termos buscados (keyword) e escopo da busca (subCategory, description).
 * - Análise de Conversão: Campo 'products' indica a quantidade de resultados retornados, vital para otimização de catálogo.
 * - Rastreamento Contextual: Vinculação com Store, Language, Customer e Category.
 * - PHP 8.4 Readiness: Tipagem nativa, interface fluida e injeção relacional via DataAccessObject.
 */
class CustomerSearch extends BaseEntity
{
    private int $storeId = 0;
    private int $languageId = 0;
    private int $customerId = 0;
    private int $categoryId = 0;
    private string $keyword = '';
    private bool $subCategory = false;
    private bool $description = false;
    private int $products = 0;
    private string $ip = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: Category::class, foreignKey: 'categoryId')]
    private ?Category $category = null;

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $storeId): self
    {
        $this->storeId = $storeId;
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

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): self
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function getKeyword(): string
    {
        return $this->keyword;
    }

    public function setKeyword(string $value): self
    {
        $this->keyword = $value;
        return $this;
    }

    public function getSubCategory(): bool
    {
        return $this->subCategory;
    }

    public function setSubCategory(bool|int $value): self
    {
        $this->subCategory = (bool)$value;
        return $this;
    }

    public function getDescription(): bool
    {
        return $this->description;
    }

    public function setDescription(bool|int $value): self
    {
        $this->description = (bool)$value;
        return $this;
    }

    public function getProducts(): int
    {
        return $this->products;
    }

    public function setProducts(int $value): self
    {
        $this->products = $value;
        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $value): self
    {
        $this->ip = $value;
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

    public function getStore(): ?Store { return $this->store; }
    public function setStore(?Store $store): self { $this->store = $store; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }

    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): self { $this->category = $category; return $this; }
}
