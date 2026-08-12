<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CategoryMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;

/**
 * CategoryRepository - Repositório central para dados de Categorias
 * 
 * Gerencia a lógica de domínio de categorias (vitrine, breadcrumbs, listagens, menus),
 * unificando a antiga e a nova engine (Slim standalone e legado).
 */
class CategoryRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Recupera os dados de uma categoria específica baseada no contexto atual.
     *
     * @param int $categoryId
     * @return array|null
     */
    public function getCategory(int $categoryId): ?array
    {
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        
        $category = $mapper->getCategory($categoryId, $this->language_id, $this->store_id);

        if (!$category) {
            return null;
        }

        return [
            'category_id'      => $category['id'] ?? $categoryId,
            'name'             => $category['name'] ?? '',
            'description'      => $category['description'] ?? '',
            'meta_title'       => $category['meta_title'] ?? '',
            'meta_description' => $category['meta_description'] ?? '',
            'meta_keyword'     => $category['meta_keyword'] ?? '',
            'image'            => $category['image'] ?? '',
            'parent_id'        => $category['parent_id'] ?? null,
        ];
    }

    /**
     * Recupera subcategorias de um determinado pai.
     *
     * @param int|null $parentId (null ou 0 para raiz)
     * @return array
     */
    public function getCategories(?int $parentId = null): array
    {
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        
        return $mapper->getSubCategories($parentId, $this->language_id, $this->store_id);
    }

    /**
     * Alpha Engine: Renderiza a árvore recursiva de categorias em HTML (Zero-Twig).
     * Com Batch Loading + Prime Cache, o menu cai de ~50 queries para apenas 2.
     */
    public function getMenuHtml(): string
    {
        $cacheKey = "category_menu_html.s{$this->store_id}.l{$this->language_id}";
        if ($this->cache && $this->cache->has($cacheKey)) {
            return (string)$this->cache->get($cacheKey);
        }

        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        
        // 1. Busca TODAS as categorias ativas de uma vez, já ordenadas (Fim do N+1 no BD)
        $flatCategories = $mapper->getAllCategories($this->language_id, $this->store_id);

        if (empty($flatCategories)) {
            return '<ul class="dropdown-menu-recursive"><li><a href="#" class="nav-link">Nenhuma categoria encontrada</a></li></ul>';
        }

        // 2. Prime Cache de SEO: Pré-carrega TODAS as URLs Amigáveis para a RAM!
        $categoryIds = array_column($flatCategories, 'id');
        $seoUrlRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(SeoUrlRepository::class);
        $seoUrlRepository->primeCache($categoryIds, 'category_id', $this->store_id, $this->language_id);

        // 3. Constrói a árvore de dependência relacional no PHP (Complexidade O(N))
        $tree = [];
        foreach ($flatCategories as $cat) {
            $parentId = $cat['parent_id'] !== null ? (int)$cat['parent_id'] : 0;
            $tree[$parentId][] = $cat;
        }

        $html = $this->buildHtmlTree($tree, 0, '', clone $seoUrlRepository);

        if ($this->cache) {
            $this->cache->set($cacheKey, $html, 3600);
        }

        return $html;
    }

    private function buildHtmlTree(array &$tree, int $parentId, string $path, SeoUrlRepository $seoUrlRepository): string
    {
        if (!isset($tree[$parentId])) return '';

        // Classes alinhadas exatamente com o seu 'personalizada.css'
        $ulClass = ($parentId === 0) ? 'dropdown-menu-recursive' : 'submenu';
        $html = '<ul class="' . $ulClass . '">';

        foreach ($tree[$parentId] as $category) {
            $catId = (int)$category['id'];
            $newPath = $path === '' ? (string)$catId : $path . '_' . $catId;
            
            // Resolução de URL relâmpago via Memória (Zero Queries Adicionais)
            $langCode = $this->config ? ($this->config->get('config_language') ?: 'pt-br') : 'pt-br';
            $keyword = $seoUrlRepository->getKeywordByQuery('category_id', (string)$catId, $this->store_id, $this->language_id);
            $href = $keyword ? "/{$langCode}/categoria/{$keyword}" : "/{$langCode}/categoria/{$catId}";

            $html .= '<li>';
            $html .= '<a href="' . $href . '">' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '</a>';

            if (isset($tree[$catId])) {
                $html .= $this->buildHtmlTree($tree, $catId, $newPath, $seoUrlRepository);
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }

    /**
     * Alpha Engine: Orquestra a inteligência de negócios e formatação da Categoria.
     * Consolida dados, subcategorias e carrega produtos via Data Mapper.
     */
    public function getCategoryData(int $categoryId, array $filterData): \Alpha\Model\DataTransferObject\ViewResponse
    {
        // 1. Busca os dados da categoria
        $categoryInfo = $this->getCategory($categoryId);

        if (!$categoryInfo) {
            return new \Alpha\Model\DataTransferObject\ViewResponse([]); // Retorna vazio para engatilhar o erro 404
        }

        $data = $categoryInfo;
        $data['description'] = html_entity_decode($categoryInfo['description'] ?? '', ENT_QUOTES, 'UTF-8');

        // 2. Breadcrumbs Brutos (Sem HTML ou roteamento físico)
        $data['breadcrumbs'] = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\CategoryMapper::class)->getBreadcrumbs($categoryId, $this->language_id, $this->store_id);

        // 3. Subcategorias Brutas
        $data['categories'] = $this->getCategories($categoryId);

        // 4. Produtos via Repositório de Domínio (Fim do N+1 Queries)
        /** @var \Alpha\Model\Domain\Repositories\ProductRepository $productRepository */
        $productRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\ProductRepository::class);
        
        $productFilter = [
            'filter_category_id'   => $categoryId,
            'filter_sub_category'  => true,
            'filter_filter'        => $filterData['filter_filter'] ?? null,
            'filter_categories'    => $filterData['categories'] ?? [],
            'filter_manufacturers' => $filterData['manufacturers'] ?? [],
            'filter_price_min'     => $filterData['price_min'] ?? null,
            'filter_price_max'     => $filterData['price_max'] ?? null,
            'filter_rating'        => $filterData['rating'] ?? null,
            'sort'                 => $filterData['sort'] ?? 'p.sort_order',
            'order'                => $filterData['order'] ?? 'ASC',
            'start'                => (($filterData['page'] ?? 1) - 1) * ($filterData['limit'] ?? 10),
            'limit'                => $filterData['limit'] ?? 10
        ];

        $data['products'] = $productRepository->getProducts($productFilter);
        $data['product_total'] = $productRepository->getTotalProducts($productFilter);

        // Parâmetros estritos de exibição
        $data['sort']  = $filterData['sort'] ?? 'p.sort_order';
        $data['order'] = $filterData['order'] ?? 'ASC';
        $data['limit'] = $filterData['limit'] ?? 10;
        $data['page']  = $filterData['page'] ?? 1;

        return new \Alpha\Model\DataTransferObject\ViewResponse($data);
    }

    // ─────────────────────────────────────────────────────────
    // SLIM CONTEXT METHODS
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna a árvore completa de categorias ativas para o menu dropdown.
     */
    public function getMenuTree(): array
    {
        $cacheKey = "category_menu_tree.s{$this->store_id}.l{$this->language_id}";
        if ($this->cache && $this->cache->has($cacheKey)) {
            return (array)$this->cache->get($cacheKey);
        }

        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        $rows = $mapper->getMenuTreeData($this->language_id, $this->store_id);

        $tree = $this->buildTree($rows);
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $tree, 3600);
        }
        return $tree;
    }

    /**
     * Retorna as categorias raiz destacadas para a grade da home page.
     *
     * @param int $limit Número máximo de categorias a exibir
     */
    public function getFeaturedCategories(int $limit = 8): array
    {
        $limitInt = max(1, (int)$limit);
        $cacheKey = "category_featured.s{$this->store_id}.l{$this->language_id}.limit{$limitInt}";
        if ($this->cache && $this->cache->has($cacheKey)) {
            return (array)$this->cache->get($cacheKey);
        }

        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        $categories = $mapper->getFeaturedCategoriesData($limitInt, $this->language_id, $this->store_id);
        if ($this->cache) {
            $this->cache->set($cacheKey, $categories, 3600);
        }
        return $categories;
    }

    /**
     * Retorna uma categoria pelo seu ID (para páginas de categoria e breadcrumbs).
     */
    public function findById(int $categoryId): ?array
    {
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        return $mapper->getCategoryWithSeo($categoryId, $this->language_id, $this->store_id);
    }

    /**
     * Constrói a árvore aninhada a partir de uma lista plana de categorias.
     */
    private function buildTree(array $rows): array
    {
        $index = [];
        foreach ($rows as $row) {
            $index[(int)$row['id']] = [
                'id'       => (int)$row['id'],
                'name'     => $row['name'],
                'url'      => $this->resolveUrl($row),
                'children' => [],
            ];
        }

        $tree = [];
        foreach ($rows as $row) {
            $id       = (int)$row['id'];
            $parentId = $row['parent_id'] !== null ? (int)$row['parent_id'] : null;

            if ($parentId === null || $parentId === 0) {
                $tree[] = &$index[$id];
            } elseif (isset($index[$parentId])) {
                $index[$parentId]['children'][] = &$index[$id];
            }
        }

        return $tree;
    }

    /**
     * Resolve a URL de uma categoria priorizando SEO keyword.
     */
    private function resolveUrl(array $row): string
    {
        $langCode = 'pt-br';
        if ($this->config) {
            $langCode = $this->config->get('config_language') ?: 'pt-br';
        }

        if (!empty($row['seo_keyword'])) {
            return "/{$langCode}/categoria/" . ltrim($row['seo_keyword'], '/');
        }

        return "/{$langCode}/categoria/" . $row['id'];
    }

    /**
     * Retorna a lista de categorias para o seletor no painel admin.
     */
    public function getCategoriesForSelect(?int $languageId = null): array
    {
        $langId = $languageId ?? $this->language_id;
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        return $mapper->getAdminCategoriesForSelect($langId);
    }

    /**
     * Cria uma nova categoria através do CategoryMapper e invalida os caches correspondentes.
     */
    public function createCategory(array $data, ?int $storeId = null, ?int $languageId = null): int
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;

        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        $categoryId = $mapper->createCategory($data, $sId, $lId);

        $this->clearCategoryCaches($categoryId, $sId, $lId);

        return $categoryId;
    }

    /**
     * Limpa os caches de categorias.
     */
    public function clearCategoryCaches(int $categoryId, ?int $storeId = null, ?int $languageId = null): void
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;

        if ($this->cache) {
            $this->cache->delete("category_menu_html.s{$sId}.l{$lId}");
            $this->cache->delete("category_menu_tree.s{$sId}.l{$lId}");
            $this->cache->delete("category.{$categoryId}.{$lId}.{$sId}");
        }
    }

    /**
     * Busca a categoria para edição no admin.
     */
    public function getCategoryForEdit(int $categoryId, ?int $languageId = null, ?int $storeId = null): ?array
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        return $mapper->getAdminCategoryForEdit($categoryId, $lId, $sId);
    }

    /**
     * Retorna a lista de categorias pai para a edição de categoria.
     */
    public function getParentCategoriesForSelect(int $categoryId, ?int $languageId = null): array
    {
        $lId = $languageId ?? $this->language_id;
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        return $mapper->getAdminParentCategories($categoryId, $lId);
    }

    /**
     * Atualiza uma categoria e limpa o cache.
     */
    public function updateCategory(int $categoryId, array $data, ?int $storeId = null, ?int $languageId = null): bool
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);

        $success = $mapper->updateCategory($categoryId, $data, $sId, $lId);
        if ($success) {
            $this->clearCategoryCaches($categoryId, $sId, $lId);
        }

        return $success;
    }

    /**
     * Exclui uma categoria e limpa o cache.
     */
    public function deleteCategory(int $categoryId, ?int $storeId = null, ?int $languageId = null): bool
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);

        $success = $mapper->deleteCategory($categoryId);
        if ($success) {
            $this->clearCategoryCaches($categoryId, $sId, $lId);
        }

        return $success;
    }

    /**
     * Retorna a lista paginada e filtrada de categorias para a tabela da listagem admin.
     */
    public function getCategoriesPaginated(array $filters, int $page = 1, int $limit = 15, ?int $languageId = null): array
    {
        $lId = $languageId ?? $this->language_id;
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        return $mapper->getAdminCategoriesPaginated($filters, $page, $limit, $lId);
    }

    // Implementações obrigatórias da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}
