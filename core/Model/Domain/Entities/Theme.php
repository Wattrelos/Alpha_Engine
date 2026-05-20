<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Theme - Gerencia a atribuição de temas visuais a rotas e lojas.
 */
class Theme extends BaseEntity
{
    private int $storeId = 0;
    private string $theme = '';
    private string $route = '';

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $id): self { $this->storeId = $id; return $this; }

    public function getTheme(): string { return $this->theme; }
    public function setTheme(string $theme): self { $this->theme = $theme; return $this; }

    public function getRoute(): string { return $this->route; }
    public function setRoute(string $route): self { $this->route = $route; return $this; }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }
}