<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade ReturnReason - Define motivos (ex: Defeito, Erro no Envio).
 */
class ReturnReason extends BaseEntity
{
    #[OneToMany(targetEntity: ReturnReasonDescription::class, foreignKey: 'returnReasonId')]
    private array $descriptions = [];

    /** @return ReturnReasonDescription[] */
    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $values): self { $this->descriptions = $values; return $this; }
}