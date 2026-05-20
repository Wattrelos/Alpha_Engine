<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductReport - Rastreia visualizações ou interações de produtos por IP/País.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Auditoria de Tráfego: Registro de origem (IP/País) para análise de conversão e segurança.
 * - Tipagem PHP 8.4: Uso de tipos nativos e inicialização de strings.
 * - Mapeamento Relacional: Atributos #[ManyToOne] para vinculação com Product e Store.
 */
class ProductReport extends BaseEntity
{
    private int $productId = 0;
    private int $storeId = 0;
    private string $ip = '';
    private string $country = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $id): self { $this->productId = $id; return $this; }

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $id): self { $this->storeId = $id; return $this; }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $value): self
    {
        $this->ip = $value;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $value): self
    {
        $this->country = $value;
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

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }

    public function getStore(): ?Store { return $this->store; }
    public function setStore(?Store $store): self { $this->store = $store; return $this; }
}
