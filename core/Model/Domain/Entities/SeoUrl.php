<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade SeoUrl - Gerencia o mapeamento de URLs amigáveis do sistema.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Indexação de Busca: Permite que rotas internas sejam traduzidas em slugs amigáveis.
 * - Tipagem PHP 8.4: Uso de tipos nativos e inicialização de strings.
 * - Mapeamento Relacional: Atributos #[ManyToOne] para vinculação com Store e Language.
 */
class SeoUrl extends BaseEntity
{
    private int $storeId = 0;
    private int $languageId = 0;
    private string $key = '';
    private string $value = '';
    private string $keyword = '';
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $id): self { $this->storeId = $id; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $id): self { $this->languageId = $id; return $this; }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $value): self
    {
        $this->key = $value;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getKeyword(): string
    {
        return $this->keyword;
    }

    public function setKeyword(string $value): self
    {
        $this->keyword = $value;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $value): self
    {
        $this->sortOrder = $value;
        return $this;
    }

    public function getStore(): ?Store { return $this->store; }
    public function setStore(?Store $store): self { $this->store = $store; return $this; }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}