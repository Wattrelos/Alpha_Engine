<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade SubscriptionStatus - Define os estados possíveis de uma assinatura.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Estrutura Multilíngue: Preparada para carregar descrições via DataAccessObject.
 * - Padronização: Segue o mesmo padrão de OrderStatus para consistência no motor de vendas.
 */
class SubscriptionStatus extends BaseEntity
{
    /**
     * @var SubscriptionStatusDescription[]
     */
    #[OneToMany(targetEntity: SubscriptionStatusDescription::class, foreignKey: 'subscriptionStatusId')]
    private array $descriptions = [];

    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $descriptions): self { $this->descriptions = $descriptions; return $this; }

    public function addDescription(SubscriptionStatusDescription $description): self { $this->descriptions[] = $description; return $this; }
}
