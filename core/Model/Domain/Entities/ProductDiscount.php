<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductDiscount - Gerencia descontos por quantidade para grupos de clientes.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Precisão Financeira: Preço tratado como float para cálculos exatos de checkout, evitando erros de arredondamento.
 * - Controle de Campanha: Campos de data e prioridade tipados para facilitar a lógica de expiração no DataMapper.
 * - Relacionamento Dinâmico: Vinculação com CustomerGroup permitindo preços segmentados de forma automatizada pelo DAO.
 */
class ProductDiscount extends BaseEntity
{
    private int $productId = 0;
    private int $customerGroupId = 0;
    private int $quantity = 0;
    private int $priority = 0;
    private float $price = 0.0000;
    private string $dateStart = '0000-00-00';
    private string $dateEnd = '0000-00-00';

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: CustomerGroup::class, foreignKey: 'customerGroupId')]
    private ?CustomerGroup $customerGroup = null;

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $value): self { $this->productId = $value; return $this; }

    public function getCustomerGroupId(): int { return $this->customerGroupId; }
    public function setCustomerGroupId(int $value): self { $this->customerGroupId = $value; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $value): self { $this->quantity = $value; return $this; }

    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $value): self { $this->priority = $value; return $this; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $value): self { $this->price = $value; return $this; }

    public function getDateStart(): string { return $this->dateStart; }
    public function setDateStart(string $value): self { $this->dateStart = $value; return $this; }

    public function getDateEnd(): string { return $this->dateEnd; }
    public function setDateEnd(string $value): self { $this->dateEnd = $value; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }

    public function getCustomerGroup(): ?CustomerGroup { return $this->customerGroup; }
    public function setCustomerGroup(?CustomerGroup $group): self { $this->customerGroup = $group; return $this; }
}