<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Coupon - Gerencia códigos de desconto e regras de utilização.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Precisão Fiscal: Campos 'discount' e 'total' tipados como float para evitar erros em descontos percentuais ou fixos.
 * - Controle Temporal: dateStart e dateEnd tipados para facilitar a lógica de validade no DataMapper.
 * - Limites de Uso: usesTotal e usesCustomer tipados como int para controle rigoroso de estoque de cupons.
 * - Cascata de Regras: #[OneToMany] preparado para carregar categorias e produtos elegíveis ao cupom.
 */
class Coupon extends BaseEntity
{
    private string $name = '';
    private string $code = '';
    private string $type = 'P';
    private float $discount = 0.0000;
    private bool $logged = false;
    private bool $shipping = false;
    private float $total = 0.0000;
    private string $dateStart = '0000-00-00';
    private string $dateEnd = '0000-00-00';
    private int $usesTotal = 0;
    private int $usesCustomer = 0;
    private bool $status = true;
    private string $dateAdded = '';

    /**
     * @var CouponCategory[]
     */
    #[OneToMany(targetEntity: CouponCategory::class, mappedBy: "coupon", foreignKey: "couponId")]
    private array $couponCategories = [];

    /**
     * @var CouponHistory[]
     */
    #[OneToMany(targetEntity: CouponHistory::class, mappedBy: "coupon", foreignKey: "couponId")]
    private array $couponHistories = [];

    /**
     * @var CouponProduct[]
     */
    #[OneToMany(targetEntity: CouponProduct::class, mappedBy: "coupon", foreignKey: "couponId")]
    private array $couponProducts = [];

    public function getName(): string { return $this->name; }
    public function setName(string $value): self { $this->name = $value; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $value): self { $this->code = $value; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $value): self { $this->type = $value; return $this; }

    public function getDiscount(): float { return $this->discount; }
    public function setDiscount(float $value): self { $this->discount = $value; return $this; }

    public function getLogged(): bool { return $this->logged; }
    public function setLogged(bool|int $value): self { $this->logged = (bool)$value; return $this; }

    public function getShipping(): bool { return $this->shipping; }
    public function setShipping(bool|int $value): self { $this->shipping = (bool)$value; return $this; }

    public function getTotal(): float { return $this->total; }
    public function setTotal(float $value): self { $this->total = $value; return $this; }

    public function getDateStart(): string { return $this->dateStart; }
    public function setDateStart(string $value): self { $this->dateStart = $value; return $this; }

    public function getDateEnd(): string { return $this->dateEnd; }
    public function setDateEnd(string $value): self { $this->dateEnd = $value; return $this; }

    public function getUsesTotal(): int { return $this->usesTotal; }
    public function setUsesTotal(int $value): self { $this->usesTotal = $value; return $this; }

    public function getUsesCustomer(): int { return $this->usesCustomer; }
    public function setUsesCustomer(int $value): self { $this->usesCustomer = $value; return $this; }

    public function getStatus(): bool { return $this->status; }
    public function setStatus(bool|int $value): self { $this->status = (bool)$value; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $value): self { $this->dateAdded = $value; return $this; }

    public function getCouponCategories(): array { return $this->couponCategories; }
    public function setCouponCategories(array $value): self { $this->couponCategories = $value; return $this; }

    public function getCouponHistories(): array { return $this->couponHistories; }
    public function setCouponHistories(array $value): self { $this->couponHistories = $value; return $this; }

    public function getCouponProducts(): array { return $this->couponProducts; }
    public function setCouponProducts(array $value): self { $this->couponProducts = $value; return $this; }
}
