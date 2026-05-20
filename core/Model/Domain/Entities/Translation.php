<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Translation - Gerencia sobreposições de tradução dinâmicas via banco de dados.
 */
class Translation extends BaseEntity
{
    private int $storeId = 0;
    private int $languageId = 0;
    private string $route = '';
    private string $key = '';
    private string $value = '';

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $id): self { $this->storeId = $id; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $id): self { $this->languageId = $id; return $this; }

    public function getRoute(): string { return $this->route; }
    public function setRoute(string $route): self { $this->route = $route; return $this; }

    public function getKey(): string { return $this->key; }
    public function setKey(string $key): self { $this->key = $key; return $this; }

    public function getValue(): string { return $this->value; }
    public function setValue(string $value): self { $this->value = $value; return $this; }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;
        return $this;
    }
}