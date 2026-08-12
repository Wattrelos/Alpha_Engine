<?php
namespace Alpha\Model\Domain\Entities\Customer;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade CustomerGroup - Define as regras para grupos de clientes.
 */
class CustomerGroup extends BaseEntity
{
    private bool $approval = false;
    private int $sortOrder = 0;

    #[OneToMany(targetEntity: CustomerGroupDescription::class, foreignKey: 'customerGroupId')]
    private array $descriptions = [];

    public function __construct() {
        parent::__construct();
        $this->descriptions = [];
    }

    public function getApproval(): bool
    {
        return $this->approval;
    }

    public function setApproval(bool|int $approval): self
    {
        $this->approval = (bool)$approval;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $descriptions): self
    {
        $this->descriptions = $descriptions;
        return $this;
    }

    // Métodos para compatibilidade com nomes legados
    public function getCustomerGroupDescription(): array {
        return $this->getDescriptions();
    }

    public function setCustomerGroupDescription(array $value): self {
        return $this->setDescriptions($value);
    }
}
