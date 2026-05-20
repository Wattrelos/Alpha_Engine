<?php

namespace Alpha\Repository;

/**
 * LayoutRepository - Gerencia a resolução de layouts e o carregamento
 * automatizado de módulos para as posições da página.
 */
class LayoutRepository extends AbstractRepository
{
    protected string $entityName = 'Layout';

    /**
     * Retorna os módulos agrupados por posição para uma determinada rota.
     * 
     * @param string $route Rota atual (ex: common/home, product/product)
     * @return array Matriz de objetos LayoutModule organizados por posição
     */
    public function getModulesForRoute(string $route): array
    {
        // 1. Resolve o ID do Layout baseado na rota
        $layoutMapper = $this->mapper->get('Layout');
        $layoutId = $layoutMapper->findIdByRoute($route);

        // Se não houver layout específico para a rota, usa o padrão do sistema
        if (!$layoutId) {
            $layoutId = (int)oc_config('config_layout_id');
        }

        // 2. Busca todos os módulos configurados para este layout
        $layoutModuleMapper = $this->mapper->get('LayoutModule');
        $allModules = $layoutModuleMapper->findAll(['layout_id' => $layoutId]);

        // 3. Organiza os módulos por posição (column_left, column_right, etc.)
        $organized = [
            'column_left'    => [],
            'column_right'   => [],
            'content_top'    => [],
            'content_bottom' => []
        ];

        foreach ($allModules as $module) {
            $position = $module->getPosition();
            if (isset($organized[$position])) {
                $organized[$position][] = $module;
            }
        }

        return $organized;
    }
}