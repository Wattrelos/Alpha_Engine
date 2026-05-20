<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Product - O "Grafo Central" do Catálogo.
 * 
 * Melhoras Alpha Engine:
 * - Hidratação Recursiva: Mapeia todas as relações necessárias para a exibição detalhada.
 * - Tipagem PHP 8.4: Proteção de tipos para cálculos e status.
 * - Interface Fluida: Facilita a construção de objetos complexos.
 */
class Product extends BaseEntity
{
    private string $model = '';
    private string $sku = '';
    private float $price = 0.0;
    private int $quantity = 0;
    private int $stockStatusId = 0;
    private string $image = '';
    private int $manufacturerId = 0;
    private int $taxClassId = 0;
    private bool $status = true;
    private int $sortOrder = 0;
    private string $dateAdded = '';
    private ?int $masterId = null;

    #[ManyToOne(targetEntity: Manufacturer::class, foreignKey: 'manufacturerId')]
    private ?Manufacturer $manufacturer = null;

    /** @var ProductDescription[] */
    #[OneToMany(targetEntity: ProductDescription::class, foreignKey: 'productId')]
    private array $descriptions = [];

    /** @var ProductImage[] */
    #[OneToMany(targetEntity: ProductImage::class, foreignKey: 'productId')]
    private array $images = [];

    /** @var ProductOption[] */
    #[OneToMany(targetEntity: ProductOption::class, foreignKey: 'productId')]
    private array $options = [];

    /** @var ProductAttribute[] */
    #[OneToMany(targetEntity: ProductAttribute::class, foreignKey: 'productId')]
    private array $attributes = [];

    /** @var ProductSpecial[] */
    #[OneToMany(targetEntity: ProductSpecial::class, foreignKey: 'productId')]
    private array $specials = [];

    /** @var ProductDiscount[] */
    #[OneToMany(targetEntity: ProductDiscount::class, foreignKey: 'productId')]
    private array $discounts = [];

    // Getters e Setters (Interface Fluida)
    public function getModel(): string { return $this->model; }
    public function setModel(string $model): self { $this->model = $model; return $this; }

    public function getSku(): string { return $this->sku; }
    public function setSku(string $sku): self { $this->sku = $sku; return $this; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): self { $this->price = $price; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): self { $this->quantity = $quantity; return $this; }

    public function getStockStatusId(): int { return $this->stockStatusId; }
    public function setStockStatusId(int $id): self { $this->stockStatusId = $id; return $this; }

    public function getImage(): string { return $this->image; }
    public function setImage(string $image): self { $this->image = $image; return $this; }

    public function getManufacturerId(): int { return $this->manufacturerId; }
    public function setManufacturerId(int $id): self { $this->manufacturerId = $id; return $this; }

    public function getTaxClassId(): int { return $this->taxClassId; }
    public function setTaxClassId(int $id): self { $this->taxClassId = $id; return $this; }

    public function getStatus(): bool { return $this->status; }
    public function setStatus(bool|int $status): self { $this->status = (bool)$status; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $order): self { $this->sortOrder = $order; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    public function getMasterId(): ?int { return $this->masterId; }
    public function setMasterId(?int $id): self { $this->masterId = $id; return $this; }

    public function getManufacturer(): ?Manufacturer { return $this->manufacturer; }
    public function setManufacturer(?Manufacturer $m): self { $this->manufacturer = $m; return $this; }

    /** @return ProductDescription[] */
    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $d): self { $this->descriptions = $d; return $this; }

    /** @return ProductImage[] */
    public function getImages(): array { return $this->images; }
    public function setImages(array $i): self { $this->images = $i; return $this; }

    /** @return ProductOption[] */
    public function getOptions(): array { return $this->options; }
    public function setOptions(array $o): self { $this->options = $o; return $this; }

    /** @return ProductAttribute[] */
    public function getAttributes(): array { return $this->attributes; }
    public function setAttributes(array $a): self { $this->attributes = $a; return $this; }

    /** @return ProductSpecial[] */
    public function getSpecials(): array { return $this->specials; }
    public function setSpecials(array $s): self { $this->specials = $s; return $this; }

    /** @return ProductDiscount[] */
    public function getDiscounts(): array { return $this->discounts; }
    public function setDiscounts(array $d): self { $this->discounts = $d; return $this; }

    /**
     * Alpha Engine Helper: Localiza o preço especial ativo para o contexto do cliente.
     * 
     * Melhora:
     * - Automatiza a validação de data (start/end).
     * - Filtra por Grupo de Cliente.
     * - Resolve o menor preço disponível (regra de prioridade do OpenCart).
     */
    public function getActiveSpecial(int $customerGroupId): float
    {
        $today = date('Y-m-d');
        $cheapestPrice = 0.0;

        foreach ($this->specials as $special) {
            if ((int)$special->getCustomerGroupId() !== $customerGroupId) {
                continue;
            }

            $start = $special->getDateStart();
            $end = $special->getDateEnd();

            // Validação de período (respeitando o padrão 0000-00-00 do banco legado)
            if (($start === '0000-00-00' || $start <= $today) && ($end === '0000-00-00' || $end >= $today)) {
                $price = (float)$special->getPrice();
                if ($cheapestPrice === 0.0 || $price < $cheapestPrice) {
                    $cheapestPrice = $price;
                }
            }
        }

        return $cheapestPrice;
    }
}