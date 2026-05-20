<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CouponProduct - Vincula produtos específicos a um cupom de desconto.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Restrição Granular: Permite cupons focados em SKUs específicos.
 * - Tipagem Estrita: Propriedades int garantem integridade na persistência via DAO.
 * - Relacionamentos: #[ManyToOne] para Coupon e Product.
 */
class CouponProduct extends BaseEntity
{
    private int $couponId = 0;
    private int $productId = 0;

    #[ManyToOne(targetEntity: Coupon::class, foreignKey: 'couponId')]
    private ?Coupon $coupon = null;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    public function getCouponId(): int { return $this->couponId; }
    public function setCouponId(int $id): self { $this->couponId = $id; return $this; }

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $id): self { $this->productId = $id; return $this; }

    public function getCoupon(): ?Coupon { return $this->coupon; }
    public function setCoupon(?Coupon $coupon): self { $this->coupon = $coupon; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }
}
