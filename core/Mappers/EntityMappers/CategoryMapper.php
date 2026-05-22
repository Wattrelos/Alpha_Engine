<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Category;
use Alpha\Mappers\EntityMappers;

/**
 * CategoryMapper - Gerencia a persistência e a hierarquia de categorias.
 * 
 * Melhoras Alpha Engine:
 * - Otimização de Breadcrumbs: Utiliza a tabela category_path para evitar recursividade no PHP.
 * - Hidratação via DAO: Retorna entidades ricas com descrições e metatags.
 * - Cache Friendly: Estrutura preparada para integração com camadas de cache de objetos.
 */
class CategoryMapper
{
    private DataAccessObject $dao;

    public function __construct()
    {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém uma categoria específica hidratada.
     */
    public function getCategory(int $categoryId, int $languageId, int $storeId): ?Category
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id')
            ->leftJoin(DB_PREFIX . 'category_to_store', 'c2s', 'c.id = c2s.category_id')
            ->where('c.id = ?', [$categoryId])
            ->where('cd.language_id = ?', [$languageId])
            ->where('c2s.store_id = ?', [$storeId])
            ->where('c.status = 1')
            ->select('c.*', 'cd.name', 'cd.description', 'cd.meta_title');

        $results = $this->dao->executeQuery($builder);
        
        if (!$results) {
            return null;
        }

        // Usamos o DAO para converter o resultado bruto em uma entidade tipada
        $category = new Category();
        $category->setId((int)$results[0]['id']);
        // O DAO preencherá as propriedades via Reflection
        $this->dao->read($category); 

        return $category;
    }

    /**
     * Gera a estrutura de Breadcrumbs de forma linear.
     * Aproveita a tabela category_path (ancestrais ordenados por nível).
     * 
     * Melhoras Alpha Engine:
     * - Integração nativa com SeoUrlMapper para links amigáveis.
     * - Prime Cache para evitar múltiplas queries de SEO.
     * 
     * @return array<int, array{name: string, href: string, id: int}>
     */
    public function getBreadcrumbs(int $categoryId, int $languageId, int $storeId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category_path', 'cp')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'cp.path_id = cd.category_id')
            ->where('cp.category_id = ?', [$categoryId])
            ->where('cd.language_id = ?', [$languageId])
            ->orderBy('cp.level', 'ASC')
            ->select('cd.name', 'cp.path_id as id');

        $rows = $this->dao->executeQuery($builder);
        
        // Alpha Engine Optimization: Prepara o cache de SEO para todos os níveis do breadcrumb de uma vez
        $pathIds = array_column($rows, 'id');
        $seoMapper = new SeoUrlMapper();
        $seoMapper->primeCache($pathIds, 'category_id', $storeId, $languageId);

        $breadcrumbs = [];
        $path = '';

        foreach ($rows as $row) {
            $pathId = (int)$row['id'];
            $path = ($path === '') ? (string)$pathId : $path . '_' . $pathId;
            
            // Resolve o slug amigável via cache
            $keyword = $seoMapper->getKeywordByQuery('category_id', (string)$pathId, $storeId, $languageId);
            
            // Se houver keyword, o link é o slug; caso contrário, usa a rota padrão
            $href = $keyword ?: 'index.php?route=product/category&path=' . $path;

            $breadcrumbs[] = [
                'name' => $row['name'],
                'href' => $href,
                'id'   => $pathId
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Lista subcategorias de um nível específico.
     */
    public function getSubCategories(int $parentId, int $languageId, int $storeId, bool $top = false): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id')
            ->leftJoin(DB_PREFIX . 'category_to_store', 'c2s', 'c.id = c2s.category_id')
            ->where('c.parent_id = ?', [$parentId])
            ->where('cd.language_id = ?', [$languageId])
            ->where('c2s.store_id = ?', [$storeId])
            ->where('c.status = 1');

        if ($top) {
            $builder->where('c.top = 1');
        }

        $builder->orderBy('c.sort_order', 'ASC')
            ->select('c.id', 'c.parent_id', 'cd.name', 'c.image', 'c.sort_order');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Obtém todas as categorias de uma vez (usado para árvores completas).
     */
    public function getAllCategories(int $languageId, int $storeId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id')
            ->leftJoin(DB_PREFIX . 'category_to_store', 'c2s', 'c.id = c2s.category_id')
            ->where('cd.language_id = ?', [$languageId])
            ->where('c2s.store_id = ?', [$storeId])
            ->where('c.status = 1')
            ->orderBy('c.parent_id', 'ASC')
            ->orderBy('c.sort_order', 'ASC')
            ->select('c.id', 'c.parent_id', 'cd.name');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Obtém o layout_id associado à categoria.
     */
    public function getLayoutId(int $categoryId, int $storeId): int
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category_to_layout')
            ->where('category_id = ?', [$categoryId])
            ->where('store_id = ?', [$storeId])
            ->select('layout_id');

        $result = $this->dao->executeQuery($builder);
        return $result ? (int)$result[0]['layout_id'] : 0;
    }

    /**
     * Obtém os grupos de filtros associados a uma categoria.
     */
    public function getCategoryFilters(int $categoryId, int $languageId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category_filter', 'cf')
            ->leftJoin(DB_PREFIX . 'filter', 'f', 'cf.filter_id = f.id')
            ->leftJoin(DB_PREFIX . 'filter_group', 'fg', 'f.filter_group_id = fg.id')
            ->leftJoin(DB_PREFIX . 'filter_group_description', 'fgd', 'fg.id = fgd.filter_group_id')
            ->where('cf.category_id = ?', [$categoryId])
            ->where('fgd.language_id = ?', [$languageId])
            ->groupBy('fg.id')
            ->orderBy('fg.sort_order', 'ASC')
            ->orderBy('LCASE(fgd.name)', 'ASC')
            ->select('fg.id', 'fgd.name', 'fg.sort_order');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Lista subcategorias hidratadas como Entidades (Batch Load).
     * @return Category[]
     */
    public function getHydratedSubCategories(int $parentId, int $languageId, int $storeId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_to_store', 'c2s', 'c.id = c2s.category_id')
            ->where('c.parent_id = ?', [$parentId])
            ->where('c2s.store_id = ?', [$storeId])
            ->where('c.status = 1')
            ->select('c.id');

        $rows = $this->dao->executeQuery($builder);
        $ids = array_column($rows, 'id');

        return $this->dao->readByIds(Category::class, $ids);
    }
}