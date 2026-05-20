<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CouponCategory - Define quais categorias são elegíveis para um cupom específico.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Segmentação Dinâmica: Permite restringir cupons a departamentos inteiros.
 * - Tipagem PHP 8.4: IDs tipados como int para integridade referencial.
 * - Mapeamento Relacional: Atributos #[ManyToOne] para Coupon e Category.
 */
class CouponCategory extends BaseEntity
{
    private int $couponId = 0;
    private int $categoryId = 0;

    #[ManyToOne(targetEntity: Coupon::class, foreignKey: 'couponId')]
    private ?Coupon $coupon = null;

    #[ManyToOne(targetEntity: Category::class, foreignKey: 'categoryId')]
    private ?Category $category = null;

    public function getCouponId(): int { return $this->couponId; }
    public function setCouponId(int $id): self { $this->couponId = $id; return $this; }

    public function getCategoryId(): int { return $this->categoryId; }
    public function setCategoryId(int $id): self { $this->categoryId = $id; return $this; }

    public function getCoupon(): ?Coupon { return $this->coupon; }
    public function setCoupon(?Coupon $coupon): self { $this->coupon = $coupon; return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): self { $this->category = $category; return $this; }
}
