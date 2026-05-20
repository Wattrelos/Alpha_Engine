<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade ReturnAction - Define ações (ex: Reembolso, Troca).
 */
class ReturnAction extends BaseEntity
{
    #[OneToMany(targetEntity: ReturnActionDescription::class, foreignKey: 'returnActionId')]
    private array $descriptions = [];

    /** @return ReturnActionDescription[] */
    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $values): self { $this->descriptions = $values; return $this; }
}