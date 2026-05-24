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
    private string $upc = '';
    private string $ean = '';
    private string $jan = '';
    private string $isbn = '';
    private string $mpn = '';
    private string $location = '';
    private string $variant = '';
    private string $override = '';
    private float $price = 0.0;
    private int $quantity = 0;
    private int $stockStatusId = 0;
    private string $image = '';
    private int $manufacturerId = 0;
    private bool $shipping = true;
    private int $points = 0;
    private int $taxClassId = 0;
    private string $dateAvailable = '';
    private float $weight = 0.0;
    private int $weightClassId = 0;
    private float $length = 0.0;
    private float $width = 0.0;
    private float $height = 0.0;
    private int $lengthClassId = 0;
    private bool $subtract = true;
    private int $minimum = 1;
    private int $rating = 0;
    private bool $status = true;
    private int $sortOrder = 0;
    private string $dateAdded = '';
    private string $dateModified = '';
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

    /** @var ProductDiscount[] */
    #[OneToMany(targetEntity: ProductDiscount::class, foreignKey: 'productId')]
    private array $discounts = [];

    // Getters e Setters (Interface Fluida)
    public function getModel(): string { return $this->model; }
    public function setModel(string $model): self { $this->model = $model; return $this; }

    public function getSku(): string { return $this->sku; }
    public function setSku(string $sku): self { $this->sku = $sku; return $this; }

    public function getUpc(): string { return $this->upc; }
    public function setUpc(string $upc): self { $this->upc = $upc; return $this; }

    public function getEan(): string { return $this->ean; }
    public function setEan(string $ean): self { $this->ean = $ean; return $this; }

    public function getJan(): string { return $this->jan; }
    public function setJan(string $jan): self { $this->jan = $jan; return $this; }

    public function getIsbn(): string { return $this->isbn; }
    public function setIsbn(string $isbn): self { $this->isbn = $isbn; return $this; }

    public function getMpn(): string { return $this->mpn; }
    public function setMpn(string $mpn): self { $this->mpn = $mpn; return $this; }

    public function getLocation(): string { return $this->location; }
    public function setLocation(string $location): self { $this->location = $location; return $this; }

    public function getVariant(): string { return $this->variant; }
    public function setVariant(string $variant): self { $this->variant = $variant; return $this; }

    public function getOverride(): string { return $this->override; }
    public function setOverride(string $override): self { $this->override = $override; return $this; }

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

    public function isShipping(): bool { return $this->shipping; }
    public function setShipping(bool|int $shipping): self { $this->shipping = (bool)$shipping; return $this; }

    public function getPoints(): int { return $this->points; }
    public function setPoints(int $points): self { $this->points = $points; return $this; }

    public function getTaxClassId(): int { return $this->taxClassId; }
    public function setTaxClassId(int $id): self { $this->taxClassId = $id; return $this; }

    public function getDateAvailable(): string { return $this->dateAvailable; }
    public function setDateAvailable(string $dateAvailable): self { $this->dateAvailable = $dateAvailable; return $this; }

    public function getWeight(): float { return $this->weight; }
    public function setWeight(float $weight): self { $this->weight = $weight; return $this; }

    public function getWeightClassId(): int { return $this->weightClassId; }
    public function setWeightClassId(int $weightClassId): self { $this->weightClassId = $weightClassId; return $this; }

    public function getLength(): float { return $this->length; }
    public function setLength(float $length): self { $this->length = $length; return $this; }

    public function getWidth(): float { return $this->width; }
    public function setWidth(float $width): self { $this->width = $width; return $this; }

    public function getHeight(): float { return $this->height; }
    public function setHeight(float $height): self { $this->height = $height; return $this; }

    public function getLengthClassId(): int { return $this->lengthClassId; }
    public function setLengthClassId(int $lengthClassId): self { $this->lengthClassId = $lengthClassId; return $this; }

    public function isSubtract(): bool { return $this->subtract; }
    public function setSubtract(bool|int $subtract): self { $this->subtract = (bool)$subtract; return $this; }

    public function getMinimum(): int { return $this->minimum; }
    public function setMinimum(int $minimum): self { $this->minimum = $minimum; return $this; }

    public function getRating(): int { return $this->rating; }
    public function setRating(int $rating): self { $this->rating = $rating; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool|int $status): self { $this->status = (bool)$status; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $order): self { $this->sortOrder = $order; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    public function getDateModified(): string { return $this->dateModified; }
    public function setDateModified(string $dateModified): self { $this->dateModified = $dateModified; return $this; }

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

    /** @return ProductDiscount[] */
    public function getDiscounts(): array { return $this->discounts; }
    public function setDiscounts(array $d): self { $this->discounts = $d; return $this; }
}