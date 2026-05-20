<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade OrderStatus - Define os estados possíveis de um pedido (ex: Pendente, Pago, Enviado).
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Normalização de Tradução: Mapeamento OneToMany para carregar nomes de status em múltiplos idiomas.
 * - Integridade de Fluxo: Centraliza a lógica de estados que o motor de checkout utiliza para disparar e-mails e baixar estoque.
 */
class OrderStatus extends BaseEntity
{
    /**
     * @var OrderStatusDescription[]
     */
    #[OneToMany(targetEntity: OrderStatusDescription::class, mappedBy: "orderStatus", foreignKey: "orderStatusId")]
    private array $descriptions = [];

    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $descriptions): self { $this->descriptions = $descriptions; return $this; }
}