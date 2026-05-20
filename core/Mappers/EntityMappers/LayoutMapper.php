<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Layout;
use Alpha\Model\Domain\Entities\LayoutModule;

/**
 * LayoutMapper - Gerencia a persistência e resolução de layouts.
 * 
 * Melhoras Alpha Engine:
 * - Hidratação via DAO: Permite recuperar layouts como entidades tipadas.
 * - Resolução de Módulos: Adiciona suporte para buscar módulos vinculados ao layout (Slideshow, Carousel, etc).
 * - Performance: Busca otimizada por ID para uso do Identity Map.
 */
class LayoutMapper extends BaseMapper
{
    protected string $entityClass = Layout::class;
    protected string $tableName = 'layout';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Resolve o ID do layout baseado na rota, suportando busca exata e wildcards.
     * 
     * Exemplo: Se a rota for 'account/login' e no banco houver 'account/%',
     * o LIKE identificará a correspondência.
     * 
     * @param string $route
     * @return int|null
     */
    public function findIdByRoute(string $route): ?int
    {
        // Utilizamos a lógica de 'melhor correspondência':
        // 1. O parâmetro ? (rota atual) é testado contra a coluna 'route' (que pode conter %)
        // 2. Ordenamos pelo tamanho da string da rota no banco (DESC).
        //    Rotas mais longas/específicas têm precedência sobre rotas genéricas.
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'layout_route')
            ->where('? LIKE route', [$route])
            ->orderBy('LENGTH(route)', 'DESC')
            ->select('layout_id')
            ->limit(1);

        $results = $this->dao->executeQuery($builder);

        return $results ? (int)$results[0]['layout_id'] : null;
    }

    /**
     * Recupera a entidade Layout hidratada.
     */
    public function getLayout(int $layoutId): ?Layout
    {
        $layout = new Layout();
        $layout->setId($layoutId);
        
        $results = $this->dao->read($layout);
        return $results ? $results[0] : null;
    }

    /**
     * Recupera os módulos configurados para este layout.
     * @return LayoutModule[]
     */
    public function getLayoutModules(int $layoutId): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'layout_module')
            ->where('layout_id = ?', [$layoutId])
            ->orderBy('position', 'ASC')
            ->orderBy('sort_order', 'ASC');

        $results = $this->dao->executeQuery($query->select('id'));
        $ids = array_map('intval', array_column($results, 'id'));

        return !empty($ids) ? $this->dao->readByIds(LayoutModule::class, $ids) : [];
    }
}