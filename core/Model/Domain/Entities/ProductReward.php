<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/*
 * Entidade ProductReward - Define a quantidade de pontos de fidelidade que um produto concede.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Segmentação de Fidelidade: Permite definir recompensas diferentes por grupo de clientes.
 * - Tipagem Estrita: Propriedades int garantem cálculos precisos no motor de recompensas.
 * - Relacionamentos: #[ManyToOne] para Product e CustomerGroup.
 */
class ProductReward extends BaseEntity
{
    private int $productId = 0;
    private int $customerGroupId = 0;
    private int $points = 0;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: CustomerGroup::class, foreignKey: 'customerGroupId')]
    private ?CustomerGroup $customerGroup = null;

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $id): self { $this->productId = $id; return $this; }

    public function getCustomerGroupId(): int { return $this->customerGroupId; }
    public function setCustomerGroupId(int $id): self { $this->customerGroupId = $id; return $this; }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $value): self
    {
        $this->points = $value;
        return $this;
    }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }

    public function getCustomerGroup(): ?CustomerGroup { return $this->customerGroup; }
    public function setCustomerGroup(?CustomerGroup $group): self { $this->customerGroup = $group; return $this; }
}
