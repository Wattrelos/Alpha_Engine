<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Gdpr - Gerencia solicitações de privacidade e exclusão de dados.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Compliance de Dados: Rastreio de ações de privacidade (exclusão, exportação).
 * - Tipagem PHP 8.4: Uso de bool para status e strings para auditoria temporal.
 * - Relacionamentos: #[ManyToOne] para vincular à Loja e Idioma.
 */
class Gdpr extends BaseEntity
{
    private int $storeId = 0;
    private int $languageId = 0;
    private string $code = '';
    private string $email = '';
    private string $action = '';
    private bool $status = false;
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $id): self { $this->storeId = $id; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $id): self { $this->languageId = $id; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getAction(): string { return $this->action; }
    public function setAction(string $action): self { $this->action = $action; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool|int $status): self { $this->status = (bool)$status; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self { $this->store = $store; return $this; }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}