<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade SubscriptionPlanDescription - Traduções dos planos de assinatura.
 */
class SubscriptionPlanDescription extends BaseEntity
{
    private string $name = '';

    #[ManyToOne(targetEntity: SubscriptionPlan::class, foreignKey: 'subscriptionPlanId')]
    private ?SubscriptionPlan $subscriptionPlan = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getSubscriptionPlanId(): int 
    { 
        return $this->subscriptionPlan ? (int)$this->subscriptionPlan->getId() : 0; 
    }
    
    public function setSubscriptionPlanId(int $id): self 
    { 
        if (!$this->subscriptionPlan) {
            $this->subscriptionPlan = new SubscriptionPlan();
        }
        $this->subscriptionPlan->setId($id); 
        return $this; 
    }

    public function getLanguageId(): int { return $this->language ? (int)$this->language->getId() : 0; }
    public function setLanguageId(int $id): self { 
        if (!$this->language) $this->language = new Language();
        $this->language->setId($id); return $this; 
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { 
        $this->language = $language; 
        return $this; 
    }
}