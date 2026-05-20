<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade LayoutRoute - Vincula layouts a rotas de URL e instâncias de loja.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Roteamento Inteligente: Permite que uma mesma rota use layouts diferentes dependendo da loja (Multi-store).
 * - Tipagem Estrita: route tipada como string para processamento exato pelo motor de URLs amigáveis.
 * - Injeção Relacional: Atributos #[ManyToOne] para que o DAO resolva os objetos Layout e Store automaticamente.
 * - Integridade Referencial: IDs tipados como int para conformidade com chaves estrangeiras.
 */
class LayoutRoute extends BaseEntity
{
    private int $layoutId = 0;
    private int $storeId = 0;
    private string $route = '';

    #[ManyToOne(targetEntity: Layout::class, foreignKey: 'layoutId')]
    private ?Layout $layout = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    public function getLayoutId(): int
    {
        return $this->layoutId;
    }

    public function setLayoutId(int $layoutId): self
    {
        $this->layoutId = $layoutId;
        return $this;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $storeId): self
    {
        $this->storeId = $storeId;
        return $this;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;
        return $this;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

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