<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Layout - Define a estrutura organizacional de uma página.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Orquestração de Página: Centraliza a associação entre rotas e módulos, permitindo que o DAO carregue a estrutura completa da página.
 * - Tipagem PHP 8.4: Nome tipado como string para garantir integridade na identificação administrativa.
 * - Relacionamentos Ativos: #[OneToMany] configurado para hidratação automática de rotas e módulos posicionados.
 * - Documentação Viva: Explica a função do layout como o ponto de decisão para a renderização de componentes.
 */
class Layout extends BaseEntity
{
    private string $name = '';

    /**
     * @var LayoutRoute[]
     */
    #[OneToMany(targetEntity: LayoutRoute::class, mappedBy: "layout", foreignKey: "layoutId")]
    private array $routes = [];

    /**
     * @var LayoutModule[]
     */
    #[OneToMany(targetEntity: LayoutModule::class, mappedBy: "layout", foreignKey: "layoutId")]
    private array $modules = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return LayoutRoute[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param LayoutRoute[] $routes
     */
    public function setRoutes(array $routes): self
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @return LayoutModule[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @param LayoutModule[] $modules
     */
    public function setModules(array $modules): self
    {
        $this->modules = $modules;
        return $this;
    }
}