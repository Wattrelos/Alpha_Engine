<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

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
    public function setStoreId(int $storeId): self { $this->storeId = $storeId; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $languageId): self { $this->languageId = $languageId; return $this; }

    public function getKey(): string { return $this->key; }
    public function setKey(string $key): self { $this->key = $key; return $this; }

    public function getValue(): string { return $this->value; }
    public function setValue(string $value): self { $this->value = $value; return $this; }

    public function getKeyword(): string { return $this->keyword; }
    public function setKeyword(string $keyword): self { $this->keyword = $keyword; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }

    public function getStore(): ?Store { return $this->store; }
    public function setStore(?Store $store): self { $this->store = $store; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}