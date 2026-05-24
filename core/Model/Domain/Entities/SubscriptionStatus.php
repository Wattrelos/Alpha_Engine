<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade SubscriptionStatus - Define os estados possíveis de uma assinatura.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Estrutura Multilíngue: Utiliza chave composta com languageId diretamente na tabela.
 * - Padronização: Segue o mesmo padrão de OrderStatus para consistência no motor de vendas.
 */
class SubscriptionStatus extends BaseEntity
{
    private int $subscriptionStatusId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getSubscriptionStatusId(): int { return $this->subscriptionStatusId; }
    public function setSubscriptionStatusId(int $value): self { $this->subscriptionStatusId = $value; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $value): self { $this->languageId = $value; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $value): self { $this->name = $value; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}
