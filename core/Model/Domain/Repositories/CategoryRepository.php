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
            'parent_id'        => $category['parent_id'] ?? 0,
        ];
    }

    /**
     * Recupera subcategorias de um determinado pai.
     *
     * @param int $parentId (0 para raiz)
     * @return array
     */
    public function getCategories(int $parentId = 0): array
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
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        
        // 1. Busca TODAS as categorias ativas de uma vez, já ordenadas (Fim do N+1 no BD)
        $flatCategories = $mapper->getAllCategories($this->language_id, $this->store_id);

        if (empty($flatCategories)) {
            return '<ul class="dropdown-menu-recursive"><li><a href="#" class="nav-link">Nenhuma categoria encontrada</a></li></ul>';
        }

        // 2. Prime Cache de SEO: Pré-carrega TODAS as URLs Amigáveis para a RAM!
        $categoryIds = array_column($flatCategories, 'id');
        $seoUrlRepository = $this->registry->get('alpha_repository_factory')->get(SeoUrlRepository::class);
        $seoUrlRepository->primeCache($categoryIds, 'category_id', $this->store_id, $this->language_id);

        // 3. Constrói a árvore de dependência relacional no PHP (Complexidade O(N))
        $tree = [];
        foreach ($flatCategories as $cat) {
            $tree[$cat['parent_id']][] = $cat;
        }

        return $this->buildHtmlTree($tree, 0, '', clone $seoUrlRepository);
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
            $keyword = $seoUrlRepository->getKeywordByQuery('category_id', (string)$catId, $this->store_id, $this->language_id);
            $href = $keyword ?: $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $newPath);

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

        // Carrega o arquivo de idiomas para o catálogo/categoria
        $languageData = $this->loadLanguage('product/category');
        $data = array_merge($categoryInfo, $languageData);

        
        // 2. SEO e Metadados
        $this->document->setTitle($categoryInfo['meta_title'] ?: $categoryInfo['name']);
        $this->document->setDescription($categoryInfo['meta_description']);
        $this->document->setKeywords($categoryInfo['meta_keyword']);

        // 3. Imagem da Categoria
        if ($categoryInfo['image'] && is_file(DIR_IMAGE . html_entity_decode($categoryInfo['image'], ENT_QUOTES, 'UTF-8'))) {
            $data['thumb'] = $this->registry->get('model_tool_image')->resize($categoryInfo['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
        } else {
            $data['thumb'] = '';
        }

        $data['description'] = html_entity_decode($categoryInfo['description'], ENT_QUOTES, 'UTF-8');

        // 3.5 Breadcrumbs Alpha Engine: Usando a tabela category_path
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home') ?: 'Home',
            'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
        ];
        $breadcrumbs = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\CategoryMapper::class)->getBreadcrumbs($categoryId, $this->language_id, $this->store_id);
        foreach ($breadcrumbs as $crumb) {
            $data['breadcrumbs'][] = ['text' => $crumb['name'], 'href' => $crumb['href']];
        }

        // 4. Subcategorias
        $data['categories'] = [];
        $subcategories = $this->getCategories($categoryId);
        
        foreach ($subcategories as $result) {
            $data['categories'][] = [
                'name' => $result['name'],
                'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $filterData['path'] . '_' . $result['id'])
            ];
        }

        // 5. Otimização de Produtos via Repositório de Domínio (Fim do N+1 Queries)
        /** @var \Alpha\Model\Domain\Repositories\ProductRepository $productRepository */
        $productRepository = $this->registry->get('alpha_repository_factory')->get(\Alpha\Model\Domain\Repositories\ProductRepository::class);
        
        $productFilter = [
            'filter_category_id'   => $categoryId,
            'filter_sub_category'  => true,
            'filter_filter'        => $filterData['filter_filter'],
            'filter_categories'    => $filterData['categories'] ?? [],
            'filter_manufacturers' => $filterData['manufacturers'] ?? [],
            'filter_price_min'     => $filterData['price_min'] ?? null,
            'filter_price_max'     => $filterData['price_max'] ?? null,
            'filter_rating'        => $filterData['rating'] ?? null,
            'sort'                 => $filterData['sort'],
            'order'                => $filterData['order'],
            'start'                => ($filterData['page'] - 1) * $filterData['limit'],
            'limit'                => $filterData['limit']
        ];

        // Delega ao Repositório do Produto a resolução de loja, idioma e descontos
        $rawProducts = $productRepository->getProducts($productFilter);
        $data['products'] = [];
        foreach ($rawProducts as $product) {
            $data['products'][] = $productRepository->getProductThumbData($product);
        }
        $data['product_total'] = $productRepository->getTotalProducts($productFilter);


        // 6. Montagem para o componente visual de Paginação
        $url = '';
        if (isset($filterData['filter_filter'])) $url .= '&filter=' . $filterData['filter_filter'];
        if (isset($filterData['sort'])) $url .= '&sort=' . $filterData['sort'];
        if (isset($filterData['order'])) $url .= '&order=' . $filterData['order'];
        if (isset($filterData['limit'])) $url .= '&limit=' . $filterData['limit'];

        // Injetar filtros facetados na URL da paginação
        if (!empty($filterData['categories'])) {
            foreach ($filterData['categories'] as $catId) {
                $url .= '&category[]=' . (int)$catId;
            }
        }
        if (!empty($filterData['manufacturers'])) {
            foreach ($filterData['manufacturers'] as $brandId) {
                $url .= '&manufacturer[]=' . (int)$brandId;
            }
        }
        if (!empty($filterData['price_min'])) $url .= '&price_min=' . urlencode($filterData['price_min']);
        if (!empty($filterData['price_max'])) $url .= '&price_max=' . urlencode($filterData['price_max']);
        if (!empty($filterData['rating'])) $url .= '&rating=' . urlencode($filterData['rating']);

        $langCode = $this->config->get('config_language') ?: 'pt-br';
        $categoryPath = '/' . $langCode . '/categoria/' . $filterData['path'];

        $data['pagination'] = [
            'total' => $data['product_total'],
            'page'  => $filterData['page'],
            'limit' => $filterData['limit'],
            'url'   => $categoryPath . '?' . ltrim($url . '&page={page}', '&')
        ];

        // URL base para os selects de ordenação e limite
        $baseUrl = '';
        if (isset($filterData['filter_filter'])) $baseUrl .= '&filter=' . $filterData['filter_filter'];

        // Injetar filtros facetados também no baseUrl para selects (ordenar e exibir)
        if (!empty($filterData['categories'])) {
            foreach ($filterData['categories'] as $catId) {
                $baseUrl .= '&category[]=' . (int)$catId;
            }
        }
        if (!empty($filterData['manufacturers'])) {
            foreach ($filterData['manufacturers'] as $brandId) {
                $baseUrl .= '&manufacturer[]=' . (int)$brandId;
            }
        }
        if (!empty($filterData['price_min'])) $baseUrl .= '&price_min=' . urlencode($filterData['price_min']);
        if (!empty($filterData['price_max'])) $baseUrl .= '&price_max=' . urlencode($filterData['price_max']);
        if (!empty($filterData['rating'])) $baseUrl .= '&rating=' . urlencode($filterData['rating']);

        // Limits
        $data['limits'] = [];
        $limits = array_unique([$this->config->get('config_pagination_catalog') ?: 10, 25, 50, 75, 100]);
        sort($limits);
        foreach($limits as $value) {
            $data['limits'][] = [
                'text'  => $value,
                'value' => $value,
                'href'  => $categoryPath . '?' . ltrim($baseUrl . '&limit=' . $value, '&')
            ];
        }

        // Sorts
        $urlWithLimit = $baseUrl . '&limit=' . $filterData['limit'];
        $data['sorts'] = [];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_default') ?: 'Padrão',
            'value' => 'p.sort_order-ASC',
            'href'  => $categoryPath . '?' . ltrim('&sort=p.sort_order&order=ASC' . $urlWithLimit, '&')
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_name_asc') ?: 'Nome (A - Z)',
            'value' => 'pd.name-ASC',
            'href'  => $categoryPath . '?' . ltrim('&sort=pd.name&order=ASC' . $urlWithLimit, '&')
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_name_desc') ?: 'Nome (Z - A)',
            'value' => 'pd.name-DESC',
            'href'  => $categoryPath . '?' . ltrim('&sort=pd.name&order=DESC' . $urlWithLimit, '&')
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_price_asc') ?: 'Preço (Menor > Maior)',
            'value' => 'p.price-ASC',
            'href'  => $categoryPath . '?' . ltrim('&sort=p.price&order=ASC' . $urlWithLimit, '&')
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_price_desc') ?: 'Preço (Maior > Menor)',
            'value' => 'p.price-DESC',
            'href'  => $categoryPath . '?' . ltrim('&sort=p.price&order=DESC' . $urlWithLimit, '&')
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_model_asc') ?: 'Modelo (A - Z)',
            'value' => 'p.model-ASC',
            'href'  => $categoryPath . '?' . ltrim('&sort=p.model&order=ASC' . $urlWithLimit, '&')
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_model_desc') ?: 'Modelo (Z - A)',
            'value' => 'p.model-DESC',
            'href'  => $categoryPath . '?' . ltrim('&sort=p.model&order=DESC' . $urlWithLimit, '&')
        ];


        // Current filters for view
        $data['sort']  = $filterData['sort'];
        $data['order'] = $filterData['order'];
        $data['limit'] = $filterData['limit'];

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
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        $rows = $mapper->getMenuTreeData($this->language_id, $this->store_id);

        return $this->buildTree($rows);
    }

    /**
     * Retorna as categorias raiz destacadas para a grade da home page.
     *
     * @param int $limit Número máximo de categorias a exibir
     */
    public function getFeaturedCategories(int $limit = 8): array
    {
        $limitInt = max(1, (int)$limit);
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        return $mapper->getFeaturedCategoriesData($limitInt, $this->language_id, $this->store_id);
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
            $parentId = (int)$row['parent_id'];

            if ($parentId === 0) {
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
        if (!empty($row['seo_keyword'])) {
            return '/' . ltrim($row['seo_keyword'], '/');
        }

        $langCode = 'pt-br';
        if ($this->registry && method_exists($this->registry, 'get')) {
            $config = $this->registry->get('config');
            if ($config) {
                $langCode = $config->get('config_language') ?: 'pt-br';
            }
        }

        return '/index.php?route=product/category&language=' . $langCode . '&path=' . $row['id'];
    }

    // Implementações obrigatórias da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}
