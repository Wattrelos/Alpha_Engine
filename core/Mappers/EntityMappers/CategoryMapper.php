<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Model\Domain\Entities\Category;

/**
 * CategoryMapper - Gerencia a persistência e a hierarquia de categorias.
 * 
 * Melhoras Alpha Engine:
 * - Otimização de Breadcrumbs: Utiliza a tabela category_path para evitar recursividade no PHP.
 * - Hidratação via DAO: Retorna entidades ricas com descrições e metatags.
 * - Cache Friendly: Estrutura preparada para integração com camadas de cache de objetos.
 */
class CategoryMapper extends BaseMapper
{
    protected string $entityClass = Category::class;
    protected string $tableName = 'category';

    /**
     * Obtém uma categoria específica hidratada.
     */
    public function getCategory(int $categoryId, int $languageId, int $storeId): ?array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id')
            ->leftJoin(DB_PREFIX . 'category_to_store', 'c2s', 'c.id = c2s.category_id')
            ->where('c.id = ?', [$categoryId])
            ->where('cd.language_id = ?', [$languageId])
            ->where('c2s.store_id = ?', [$storeId])
            ->where('c.status = 1')
            ->select('c.*', 'cd.name', 'cd.description', 'cd.meta_title', 'cd.meta_description', 'cd.meta_keyword');

        $results = $this->dao->executeQuery($builder);
        
        return $results ? $results[0] : null;
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
            
            // Se houver keyword, o link é o slug; caso contrário, usa a nova rota Slim
            $langCode = ($languageId === 1) ? 'en' : 'pt-br';
            $href = $keyword ? "/{$langCode}/categoria/{$keyword}" : "/{$langCode}/categoria/{$pathId}";

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
    public function getSubCategories(?int $parentId, int $languageId, int $storeId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id')
            ->leftJoin(DB_PREFIX . 'category_to_store', 'c2s', 'c.id = c2s.category_id');

        if ($parentId === null || $parentId === 0) {
            $builder->where('c.parent_id IS NULL');
        } else {
            $builder->where('c.parent_id = ?', [$parentId]);
        }

        $builder->where('cd.language_id = ?', [$languageId])
            ->where('c2s.store_id = ?', [$storeId])
            ->where('c.status = 1');

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
    public function getHydratedSubCategories(?int $parentId, int $languageId, int $storeId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_to_store', 'c2s', 'c.id = c2s.category_id');

        if ($parentId === null || $parentId === 0) {
            $builder->where('c.parent_id IS NULL');
        } else {
            $builder->where('c.parent_id = ?', [$parentId]);
        }

        $builder->where('c2s.store_id = ?', [$storeId])
            ->where('c.status = 1')
            ->select('c.id');

        $rows = $this->dao->executeQuery($builder);
        $ids = array_column($rows, 'id');

        return $this->dao->readByIds(Category::class, $ids);
    }

    /**
     * Retorna a lista plana de categorias para montagem do menu tree.
     */
    public function getMenuTreeData(int $languageId, int $storeId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->join(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id')
            ->join(DB_PREFIX . 'category_to_store', 'cs', 'c.id = cs.category_id')
            ->leftJoin(
                DB_PREFIX . 'seo_url',
                'su',
                "su.`key` = 'category_id' AND su.value = c.id AND su.store_id = " . (int)$storeId . " AND su.language_id = " . (int)$languageId
            )
            ->where('cd.language_id = ?', [$languageId])
            ->where('cs.store_id = ?', [$storeId])
            ->where('c.status = 1')
            ->orderBy('c.sort_order', 'ASC')
            ->orderBy('cd.name', 'ASC')
            ->select('c.id', 'c.parent_id', 'cd.name', "IFNULL(su.keyword, '') AS seo_keyword");

        return $this->dao->executeQuery($builder);
    }

    /**
     * Retorna as categorias raiz destacadas.
     */
    public function getFeaturedCategoriesData(int $limit, int $languageId, int $storeId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->join(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id')
            ->join(DB_PREFIX . 'category_to_store', 'cs', 'c.id = cs.category_id')
            ->leftJoin(DB_PREFIX . 'category', 'sub', 'sub.parent_id = c.id AND sub.status = 1')
            ->leftJoin(
                DB_PREFIX . 'seo_url',
                'su',
                "su.`key` = 'category_id' AND su.value = c.id AND su.store_id = " . (int)$storeId . " AND su.language_id = " . (int)$languageId
            )
            ->where('cd.language_id = ?', [$languageId])
            ->where('cs.store_id = ?', [$storeId])
            ->where('c.status = 1')
            ->where('c.parent_id IS NULL')
            ->groupBy('c.id')
            ->groupBy('c.image')
            ->groupBy('cd.name')
            ->groupBy('su.keyword')
            ->orderBy('c.sort_order', 'ASC')
            ->orderBy('cd.name', 'ASC')
            ->limit($limit)
            ->select('c.id', 'c.image', 'cd.name', "IFNULL(su.keyword, '') AS seo_keyword", 'COUNT(sub.id) AS subcategory_count');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Obtém uma categoria específica com seo_keyword.
     */
    public function getCategoryWithSeo(int $categoryId, int $languageId, int $storeId): ?array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->join(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id')
            ->leftJoin(
                DB_PREFIX . 'seo_url',
                'su',
                "su.`key` = 'category_id' AND su.value = c.id AND su.store_id = " . (int)$storeId . " AND su.language_id = " . (int)$languageId
            )
            ->where('c.id = ?', [$categoryId])
            ->where('cd.language_id = ?', [$languageId])
            ->where('c.status = 1')
            ->select(
                'c.id',
                'c.parent_id',
                'c.image',
                'cd.name',
                'cd.description',
                'cd.meta_title',
                'cd.meta_description',
                'cd.meta_keyword',
                "IFNULL(su.keyword, '') AS seo_keyword"
            );

        $results = $this->dao->executeQuery($builder);
        return $results ? $results[0] : null;
    }

    /**
     * Retorna lista de categorias formatada para o campo de seleção do Admin.
     */
    public function getAdminCategoriesForSelect(int $languageId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id AND cd.language_id = ' . (int)$languageId)
            ->orderBy('cd.name', 'ASC')
            ->select('c.id', 'cd.name');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Cria uma nova categoria e seus registros correlatos de forma atômica.
     */
    public function createCategory(array $data, int $storeId, int $languageId): int
    {
        $uow = new UnitOfWork();
        $categoryId = 0;

        $uow->transaction(function () use ($data, $storeId, $languageId, &$categoryId) {
            $image = $data['image'] ?? '';
            $parentId = (isset($data['parent_id']) && (int)$data['parent_id'] > 0) ? (int)$data['parent_id'] : null;
            $sortOrder = (int)($data['sort_order'] ?? 0);
            $status = isset($data['status']) ? (int)$data['status'] : 1;

            // 1. Inserção na tabela category via executeRawSQL
            $sqlCat = "INSERT INTO `" . DB_PREFIX . "category` (`image`, `parent_id`, `sort_order`, `status`) VALUES (?, ?, ?, ?)";
            $this->dao->executeRawSQL($sqlCat, [$image, $parentId, $sortOrder, $status]);
            
            $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
            $categoryId = (int)$conn->lastInsertId();

            // 2. Inserção nas descrições de idioma
            $stmtLangs = $conn->query("SELECT id FROM `" . DB_PREFIX . "language`");
            $languages = $stmtLangs->fetchAll(\PDO::FETCH_COLUMN);

            $name = trim($data['name'] ?? '');
            $description = $data['description'] ?? '';
            $metaTitle = trim($data['meta_title'] ?? '');
            if (empty($metaTitle)) {
                $metaTitle = $name;
            }
            $metaDescription = trim($data['meta_description'] ?? '');
            $metaKeyword = trim($data['meta_keyword'] ?? '');

            $sqlDesc = "INSERT INTO `" . DB_PREFIX . "category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            foreach ($languages as $langId) {
                $this->dao->executeRawSQL($sqlDesc, [$categoryId, $langId, $name, $description, $metaTitle, $metaDescription, $metaKeyword]);
            }

            // 3. Inserção em category_to_store
            $effectiveStoreId = $storeId > 0 ? $storeId : 1;
            $sqlStore = "INSERT INTO `" . DB_PREFIX . "category_to_store` (`category_id`, `store_id`) VALUES (?, ?)";
            $this->dao->executeRawSQL($sqlStore, [$categoryId, $effectiveStoreId]);

            // 4. Reconstrução dos caminhos da categoria (category_path)
            $this->rebuildCategoryPaths($categoryId, $parentId);

            // 5. Inserção da SEO URL (se fornecida)
            $seoKeyword = trim($data['seo_keyword'] ?? '');
            if (!empty($seoKeyword)) {
                $sqlSeo = "INSERT INTO `" . DB_PREFIX . "seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES (?, ?, 'category_id', ?, ?)";
                $this->dao->executeRawSQL($sqlSeo, [$storeId, $languageId, $categoryId, $seoKeyword]);
            }
        });

        return $categoryId;
    }

    /**
     * Reconstrói os caminhos (category_path) para manter a hierarquia.
     */
    public function rebuildCategoryPaths(int $categoryId, ?int $parentId): void
    {
        $sqlDel = "DELETE FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = ?";
        $this->dao->executeRawSQL($sqlDel, [$categoryId]);

        $paths = [];
        if ($parentId !== null && $parentId > 0) {
            $builder = (new QueryBuilder())
                ->from(DB_PREFIX . 'category_path')
                ->where('category_id = ?', [$parentId])
                ->orderBy('level', 'ASC')
                ->select('path_id', 'level');
            $paths = $this->dao->executeQuery($builder);
        }

        $level = 0;
        $sqlIns = "INSERT INTO `" . DB_PREFIX . "category_path` (`category_id`, `path_id`, `level`) VALUES (?, ?, ?)";
        foreach ($paths as $path) {
            $this->dao->executeRawSQL($sqlIns, [$categoryId, $path['path_id'], $level]);
            $level++;
        }

        $this->dao->executeRawSQL($sqlIns, [$categoryId, $categoryId, $level]);
    }

    /**
     * Busca os dados completos de uma categoria para edição no Admin.
     */
    public function getAdminCategoryForEdit(int $categoryId, int $languageId, int $storeId): ?array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id AND cd.language_id = ' . (int)$languageId)
            ->where('c.id = ?', [$categoryId])
            ->select(
                'c.*',
                'cd.name',
                'cd.description',
                'cd.meta_title',
                'cd.meta_description',
                'cd.meta_keyword',
                "(SELECT keyword FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'category_id' AND `value` = CAST(c.id AS CHAR) AND store_id = " . (int)$storeId . " AND language_id = " . (int)$languageId . " LIMIT 1) AS seo_keyword"
            );

        $results = $this->dao->executeQuery($builder);
        return $results ? $results[0] : null;
    }

    /**
     * Retorna a lista de categorias pai disponíveis (excluindo a própria e descendentes).
     */
    public function getAdminParentCategories(int $categoryId, int $languageId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id AND cd.language_id = ' . (int)$languageId)
            ->where('c.id NOT IN (SELECT category_id FROM `' . DB_PREFIX . 'category_path` WHERE path_id = ?)', [$categoryId])
            ->orderBy('cd.name', 'ASC')
            ->select('c.id', 'cd.name');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Atualiza uma categoria e suas tabelas relacionadas de forma atômica.
     */
    public function updateCategory(int $categoryId, array $data, int $storeId, int $languageId): bool
    {
        $uow = new UnitOfWork();

        return (bool)$uow->transaction(function () use ($categoryId, $data, $storeId, $languageId) {
            $existing = $this->getAdminCategoryForEdit($categoryId, $languageId, $storeId);
            if (!$existing) {
                return false;
            }

            $currentImagePath = $existing['image'] ?? '';
            $removeImage = isset($data['remove_image']) && $data['remove_image'] == '1';
            $newImagePath = $removeImage ? '' : (!empty($data['image']) ? $data['image'] : $currentImagePath);

            $parentId = (isset($data['parent_id']) && (int)$data['parent_id'] > 0) ? (int)$data['parent_id'] : null;
            $sortOrder = (int)($data['sort_order'] ?? 0);
            $status = isset($data['status']) ? (int)$data['status'] : 1;

            // 1. Atualiza a tabela category
            $sqlUpd = "UPDATE `" . DB_PREFIX . "category` SET `image` = ?, `parent_id` = ?, `sort_order` = ?, `status` = ? WHERE `id` = ?";
            $this->dao->executeRawSQL($sqlUpd, [$newImagePath, $parentId, $sortOrder, $status, $categoryId]);

            // 2. Atualiza ou insere category_description para todos os idiomas
            $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
            $stmtLangs = $conn->query("SELECT id FROM `" . DB_PREFIX . "language`");
            $languages = $stmtLangs->fetchAll(\PDO::FETCH_COLUMN);

            $name = trim($data['name'] ?? '');
            $description = $data['description'] ?? '';
            $metaTitle = trim($data['meta_title'] ?? '');
            if (empty($metaTitle)) {
                $metaTitle = $name;
            }
            $metaDescription = trim($data['meta_description'] ?? '');
            $metaKeyword = trim($data['meta_keyword'] ?? '');

            foreach ($languages as $langId) {
                $checkBuilder = (new QueryBuilder())
                    ->from(DB_PREFIX . 'category_description')
                    ->where('category_id = ?', [$categoryId])
                    ->where('language_id = ?', [$langId]);

                $exists = $this->dao->executeCount($checkBuilder) > 0;

                if ($exists) {
                    $sqlDesc = "UPDATE `" . DB_PREFIX . "category_description` SET `name` = ?, `description` = ?, `meta_title` = ?, `meta_description` = ?, `meta_keyword` = ? WHERE `category_id` = ? AND `language_id` = ?";
                    $this->dao->executeRawSQL($sqlDesc, [$name, $description, $metaTitle, $metaDescription, $metaKeyword, $categoryId, $langId]);
                } else {
                    $sqlDesc = "INSERT INTO `" . DB_PREFIX . "category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $this->dao->executeRawSQL($sqlDesc, [$categoryId, $langId, $name, $description, $metaTitle, $metaDescription, $metaKeyword]);
                }
            }

            // 3. Reconstrói os caminhos se o pai foi alterado
            $oldParentId = $existing['parent_id'] !== null ? (int)$existing['parent_id'] : null;
            if ($oldParentId !== $parentId) {
                $this->rebuildCategoryPathsAndSubcategories($categoryId, $parentId);
            }

            // 4. Atualiza SEO URL
            $sqlSeoDel = "DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'category_id' AND `value` = CAST(? AS CHAR)";
            $this->dao->executeRawSQL($sqlSeoDel, [$categoryId]);

            $seoKeyword = trim($data['seo_keyword'] ?? '');
            if (!empty($seoKeyword)) {
                $sqlSeo = "INSERT INTO `" . DB_PREFIX . "seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES (?, ?, 'category_id', CAST(? AS CHAR), ?)";
                $this->dao->executeRawSQL($sqlSeo, [$storeId, $languageId, $categoryId, $seoKeyword]);
            }

            return true;
        });
    }

    /**
     * Reconstrução recursiva de caminhos para a categoria e todas as suas subcategorias.
     */
    public function rebuildCategoryPathsAndSubcategories(int $categoryId, ?int $parentId): void
    {
        $this->rebuildCategoryPaths($categoryId, $parentId);

        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category')
            ->where('parent_id = ?', [$categoryId])
            ->select('id');
        $subs = $this->dao->executeQuery($builder);

        foreach ($subs as $sub) {
            $subId = (int)$sub['id'];
            $this->rebuildCategoryPathsAndSubcategories($subId, $categoryId);
        }
    }

    /**
     * Exclui uma categoria e ajusta suas subcategorias de forma atômica.
     */
    public function deleteCategory(int $categoryId): bool
    {
        $uow = new UnitOfWork();

        return (bool)$uow->transaction(function () use ($categoryId) {
            // 1. Busca subcategorias diretas
            $subBuilder = (new QueryBuilder())
                ->from(DB_PREFIX . 'category')
                ->where('parent_id = ?', [$categoryId])
                ->select('id');
            $subs = $this->dao->executeQuery($subBuilder);

            // 2. Transfere subcategorias para a raiz (parent_id = NULL)
            $sqlUpdSubs = "UPDATE `" . DB_PREFIX . "category` SET `parent_id` = NULL WHERE `parent_id` = ?";
            $this->dao->executeRawSQL($sqlUpdSubs, [$categoryId]);

            // 3. Reconstrói os caminhos das subcategorias que foram para a raiz
            foreach ($subs as $sub) {
                $this->rebuildCategoryPaths((int)$sub['id'], null);
            }

            // 4. Remove vínculos de category_path
            $sqlPathDel = "DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = ? OR path_id = ?";
            $this->dao->executeRawSQL($sqlPathDel, [$categoryId, $categoryId]);

            // 5. Remove descrições
            $sqlDescDel = "DELETE FROM `" . DB_PREFIX . "category_description` WHERE category_id = ?";
            $this->dao->executeRawSQL($sqlDescDel, [$categoryId]);

            // 6. Remove relação com loja
            $sqlStoreDel = "DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE category_id = ?";
            $this->dao->executeRawSQL($sqlStoreDel, [$categoryId]);

            // 7. Remove SEO URL
            $sqlSeoDel = "DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'category_id' AND `value` = CAST(? AS CHAR)";
            $this->dao->executeRawSQL($sqlSeoDel, [$categoryId]);

            // 8. Remove registro principal
            $sqlCatDel = "DELETE FROM `" . DB_PREFIX . "category` WHERE id = ?";
            $this->dao->executeRawSQL($sqlCatDel, [$categoryId]);

            return true;
        });
    }

    /**
     * Retorna lista paginada e filtrada de categorias para a listagem do Admin.
     */
    public function getAdminCategoriesPaginated(array $filters, int $page, int $limit, int $languageId): array
    {
        $start = max(0, ($page - 1) * $limit);

        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'category', 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id AND cd.language_id = ' . (int)$languageId);

        if (!empty($filters['filter_name'])) {
            $builder->where('cd.name LIKE ?', ['%' . $filters['filter_name'] . '%']);
        }

        if (isset($filters['filter_status']) && $filters['filter_status'] !== '') {
            $builder->where('c.status = ?', [(int)$filters['filter_status']]);
        }

        $count = $this->dao->executeCount($builder);

        $builder->select(
            'c.id',
            'c.image',
            'cd.name',
            'c.sort_order',
            'c.status',
            "(SELECT cd2.name FROM `" . DB_PREFIX . "category_description` cd2 WHERE cd2.category_id = c.parent_id AND cd2.language_id = " . (int)$languageId . " LIMIT 1) AS parent_name"
        )
        ->orderBy('cd.name', 'ASC')
        ->limit($limit)
        ->offset($start);

        $rows = $this->dao->executeQuery($builder);

        return [
            'total' => $count,
            'data'  => $rows
        ];
    }
}