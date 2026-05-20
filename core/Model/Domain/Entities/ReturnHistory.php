<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ReturnHistory - Registra o histórico de alterações no status de uma devolução.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Trilha de Auditoria: Registro cronológico de mudanças de status e comunicações.
 * - Tipagem PHP 8.4: Uso de bool para sinalização de notificação e int para chaves estrangeiras.
 * - Mapeamento Relacional: Vinculação ManyToOne com OrderReturn para rastreabilidade do processo.
 * - Interface Fluida: Setters preparados para encadeamento durante o registro de eventos.
 */
class ReturnHistory extends BaseEntity
{
    private bool $notify = false;
    private string $comment = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: OrderReturn::class, foreignKey: 'orderReturnId')]
    private ?OrderReturn $orderReturn = null;

    #[ManyToOne(targetEntity: ReturnStatus::class, foreignKey: 'returnStatusId')]
    private ?ReturnStatus $returnStatus = null;

    public function getOrderReturnId(): int 
    { 
        return $this->orderReturn ? (int)$this->orderReturn->getId() : 0; 
    }
    
    public function setOrderReturnId(int $id): self 
    { 
        if (!$this->orderReturn) $this->orderReturn = new OrderReturn();
        $this->orderReturn->setId($id); 
        return $this; 
    }

    public function getReturnStatusId(): int { return $this->returnStatus ? (int)$this->returnStatus->getId() : 0; }
    public function setReturnStatusId(int $id): self { 
        if (!$this->returnStatus) $this->returnStatus = new ReturnStatus();
        $this->returnStatus->setId($id); return $this; 
    }

    public function isNotify(): bool { return $this->notify; }
    public function setNotify(bool|int $value): self { $this->notify = (bool)$value; return $this; }

    public function getComment(): string { return $this->comment; }
    public function setComment(string $comment): self { $this->comment = $comment; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    /**
     * Retorna o objeto de devolução associado.
     */
    public function getOrderReturn(): ?OrderReturn { return $this->orderReturn; }

    /**
     * Injeta o objeto de devolução associado.
     */
    public function setOrderReturn(?OrderReturn $orderReturn): self 
    { 
        $this->orderReturn = $orderReturn; 
        return $this; 
    }
}