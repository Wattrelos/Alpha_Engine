<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\LayoutMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * LayoutRepository - Autoridade de Domínio para Layouts.
 *
 * Centraliza o acesso aos dados de layouts, utilizando o LayoutMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class LayoutRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = LayoutMapper::class;
    private array $routeModulesCache = [];

    /**
     * Busca um layout pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        return $this->mapperFactory->get(LayoutMapper::class)->findById($id);
    }

    /**
     * Retorna todos os layouts ativos no sistema.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        return $this->mapperFactory->get(LayoutMapper::class)->findAll();
    }

    /**
     * Busca layouts baseado em critérios específicos.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get(LayoutMapper::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca um único layout baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return $this->mapperFactory->get(LayoutMapper::class)->findOneBy($criteria);
    }

    /**
     * Retorna os módulos agrupados por posição para uma determinada rota.
     * Permite sobreposição do ID do Layout (útil para Categorias, Produtos, etc).
     * 
     * @param string $route Rota atual (ex: common/home, product/product)
     * @param int|null $overrideLayoutId ID do layout específico (se já resolvido pelo controller ou entidade)
     * @return array Matriz de objetos LayoutModule organizados por posição
     */
    public function getModulesForRoute(string $route, ?int $overrideLayoutId = null): array
    {
        // Alpha Engine Failsafe: Previne N+1 Queries (O controlador base chama 4x para cada posição)
        $cacheKey = $route . '_' . ($overrideLayoutId ?? 0);
        if (isset($this->routeModulesCache[$cacheKey])) {
            return $this->routeModulesCache[$cacheKey];
        }

        $layoutId = $overrideLayoutId;
        
        if (!$layoutId) {
            $layoutMapper = $this->mapperFactory->get(LayoutMapper::class);
            
            // Verifica se o mapper suporta findIdByRoute com segurança
            $layoutId = method_exists($layoutMapper, 'findIdByRoute') 
                ? $layoutMapper->findIdByRoute($route, (int)$this->config->get('config_store_id')) 
                : 0;
        }

        // Se não houver layout específico para a rota, usa o padrão do sistema
        if (!$layoutId) {
            $layoutId = (int)$this->config->get('config_layout_id');
        }

        // Alpha Engine: Cache Físico para evitar bater na tabela layout_module em todas as requisições
        $globalCacheKey = "layout.modules.{$layoutId}";
        if ($this->cache !== null && $this->cache->has($globalCacheKey)) {
            $organized = $this->cache->get($globalCacheKey);
            $this->routeModulesCache[$cacheKey] = $organized;
            return $organized;
        }

        $organized = [
            'column_left'    => [],
            'column_right'   => [],
            'content_top'    => [],
            'content_bottom' => []
        ];

        $layoutModuleMapper = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\LayoutModuleMapper::class);
        $allModules = $layoutModuleMapper->findModulesByLayoutId($layoutId);
        
        // Garante que os módulos respeitem a ordem (sort_order) configurada no painel administrativo
        usort($allModules, function($a, $b) {
            return (int)($a['sort_order'] ?? 0) <=> (int)($b['sort_order'] ?? 0);
        });

        foreach ($allModules as $module) {
            if (isset($organized[$module['position']])) {
                $organized[$module['position']][] = $module;
            }
        }

        if ($this->cache !== null) {
            $this->cache->set($globalCacheKey, $organized, 86400); // 24h de cache
        }

        $this->routeModulesCache[$cacheKey] = $organized;

        return $organized;
    }

    /**
     * Legacy Bridge: Compatibilidade com Controladores Legados (model_design_layout->getModules).
     */
    public function getModules(int $layoutId, string $position): array
    {
        $modules = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\LayoutModuleMapper::class)
                                       ->findModulesByLayoutId($layoutId);
        
        $filtered = array_filter($modules, fn($m) => $m['position'] === $position);
        
        // Aplica a ordenação também na ponte legada para evitar quebras de visual
        usort($filtered, function($a, $b) {
            return (int)($a['sort_order'] ?? 0) <=> (int)($b['sort_order'] ?? 0);
        });

        return array_values($filtered);
    }

    /**
     * Retorna os módulos carregados para uma determinada rota e tipo (ex: analytics).
     * 
     * @param string $route Rota atual
     * @param string $type Tipo de módulo (analytics, captcha, etc)
     * @return array
     */
    public function getModulesByRoute(string $route, string $type): array
    {
        // TODO: Implementar a consulta real ao Mapper para o tipo específico
        // Retornando array vazio temporariamente para desobstruir a view
        return [];
    }

    /**
     * Retorna os estilos (CSS) registrados para a rota atual.
     * Temporariamente recebe o objeto Document por injeção de método para não quebrar o layout.
     */
    public function getStylesByRoute(string $route, $document = null): array
    {
        // TODO: Implementar a resolução avançada de Assets pelo Layout (Alpha Engine)
        return $document ? $document->getStyles() : [];
    }

    /**
     * Retorna os scripts (JS) registrados para a rota atual.
     */
    public function getScriptsByRoute(string $route, string $position = 'header', $document = null): array
    {
        // TODO: Implementar a resolução avançada de Assets pelo Layout (Alpha Engine)
        $scripts = $document ? $document->getScripts($position) : [];
        $formattedScripts = [];
        
        foreach ($scripts as $script) {
            if (is_array($script)) {
                $formattedScripts[] = $script;
            } elseif (is_string($script)) {
                $formattedScripts[] = ['href' => $script];
            }
        }
        
        return $formattedScripts;
    }

    /**
     * Retorna os links (canonical, rel) registrados para a rota atual.
     */
    public function getLinksByRoute(string $route, $document = null): array
    {
        // TODO: Implementar a resolução avançada de Assets pelo Layout (Alpha Engine)
        return $document ? $document->getLinks() : [];
    }

}