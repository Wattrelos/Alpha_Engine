<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade SubscriptionHistory - Registra mudanças de estado e comunicações de uma assinatura.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Rastreabilidade: Histórico completo de transições de status com comentários.
 * - Tipagem PHP 8.4: Uso de bool para sinalizadores de notificação e int para chaves.
 * - Relacionamentos: #[ManyToOne] para Subscription e SubscriptionStatus.
 */
class SubscriptionHistory extends BaseEntity
{
    private string $comment = '';
    private bool $notify = false;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Subscription::class, foreignKey: 'subscriptionId')]
    private ?Subscription $subscription = null;

    #[ManyToOne(targetEntity: SubscriptionStatus::class, foreignKey: 'subscriptionStatusId')]
    private ?SubscriptionStatus $subscriptionStatus = null;

    public function getSubscriptionId(): int { return $this->subscription ? (int)$this->subscription->getId() : 0; }
    public function setSubscriptionId(int $id): self { 
        if (!$this->subscription) $this->subscription = new Subscription();
        $this->subscription->setId($id); return $this; 
    }

    public function getSubscriptionStatusId(): int 
    { 
        return $this->subscriptionStatus ? (int)$this->subscriptionStatus->getId() : 0; 
    }
    
    public function setSubscriptionStatusId(int $id): self { 
        if (!$this->subscriptionStatus) $this->subscriptionStatus = new SubscriptionStatus();
        $this->subscriptionStatus->setId($id); return $this; 
    }

    public function getComment(): string { return $this->comment; }
    public function setComment(string $comment): self { $this->comment = $comment; return $this; }

    public function isNotify(): bool { return $this->notify; }
    public function setNotify(bool $notify): self { $this->notify = $notify; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function getSubscriptionStatus(): ?SubscriptionStatus
    {
        return $this->subscriptionStatus;
    }

    public function setSubscriptionStatus(?SubscriptionStatus $status): self
    {
        $this->subscriptionStatus = $status;
        return $this;
    }
}