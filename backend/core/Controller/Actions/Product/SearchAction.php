<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Container\ContainerInterface;
use Alpha\Support\Presenters\ImagePresenter;

class SearchAction implements ActionInterface
{
    private ProductRepository $productRepository;
    private SeoUrlRepository $seoRepository;
    private CategoryRepository $categoryRepository;
    private ManufacturerRepository $manufacturerRepository;
    private TwigEnvironment $twig;
    private \Alpha\Support\Language $translator;
    private ContainerInterface $container;
    private ImagePresenter $imagePresenter;

    public function __construct(
        ProductRepository $productRepository,
        SeoUrlRepository $seoRepository,
        CategoryRepository $categoryRepository,
        ManufacturerRepository $manufacturerRepository,
        TwigEnvironment $twig,
        \Alpha\Support\Language $translator,
        ContainerInterface $container,
        ImagePresenter $imagePresenter
    ) {
        $this->productRepository = $productRepository;
        $this->seoRepository = $seoRepository;
        $this->categoryRepository = $categoryRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->container = $container;
        $this->imagePresenter = $imagePresenter;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->translator->load('product/search');
        $this->twig->addGlobal('Search', $this->translator->getNestedData('product/search'));

        $queryParams = $request->getQueryParams();
        
        $filterData = [
            'search'             => $queryParams['busca'] ?? $queryParams['search'] ?? '',
            'filter_name'        => $queryParams['busca'] ?? $queryParams['search'] ?? '',
            'filter_description' => $queryParams['description'] ?? '',
            'filter_category_id' => (int)($queryParams['category_id'] ?? 0),
            'filter_sub_category'=> $queryParams['sub_category'] ?? '',
            'sort'               => $queryParams['sort'] ?? 'p.sort_order',
            'order'              => $queryParams['order'] ?? 'ASC',
            'page'               => max(1, (int)($queryParams['page'] ?? 1)),
            'limit'              => max(1, (int)($queryParams['limit'] ?? 12)),
            
            // Filtros facetados
            'filter_categories'    => $queryParams['category'] ?? [],
            'filter_manufacturers' => $queryParams['manufacturer'] ?? [],
            'filter_price_min'     => $queryParams['price_min'] ?? null,
            'filter_price_max'     => $queryParams['price_max'] ?? null,
            'filter_rating'        => $queryParams['rating'] ?? null
        ];

        // Obtém dados de busca do repositório
        $viewResponse = $this->productRepository->getSearchData($filterData);
        $data = $viewResponse->getData();

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');
        $languageId = $request->getAttribute('language_id', 2);

        $config = $this->container->has('config') ? $this->container->get('config') : null;
        $currency = $this->container->has('currency') ? $this->container->get('currency') : null;
        $tax = $this->container->has('tax') ? $this->container->get('tax') : null;
        $session = $this->container->has('session') ? $this->container->get('session') : null;
        $currencyCode = $session->data['currency'] ?? ($config ? $config->get('config_currency') : 'BRL');
        $imagePresenter = $this->imagePresenter;

        // Garante que o href, slug, imagens e preços dos produtos da busca sejam construídos
        if (isset($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as &$product) {
                $productId = (int)($product['product_id'] ?? $product['id'] ?? 0);

                // Normaliza a chave para que o template acesse prod.product_id independente do nome da coluna no BD
                if (!isset($product['product_id']) && isset($product['id'])) {
                    $product['product_id'] = $product['id'];
                }

                $keyword = $productId > 0 ? $this->seoRepository->getKeywordByQuery('product_id', $productId, 0, $languageId) : '';
                
                $productSlug = !empty($keyword) ? $keyword : (!empty($product['keyword']) ? $product['keyword'] : $productId);
                $product['slug'] = $productSlug;
                $product['href'] = $routeParser->urlFor('product.detail', ['lang' => $lang, 'slug' => (string)$productSlug]);

                // Exibe nome e imagem da variação de menor preço caso existam variações com preços distintos
                $minPrice = !empty($product['min_variant_price']) ? (float)$product['min_variant_price'] : null;
                $maxPrice = !empty($product['max_variant_price']) ? (float)$product['max_variant_price'] : null;
                if ($minPrice !== null && $maxPrice !== null && $minPrice !== $maxPrice) {
                    if (!empty($product['min_variant_name'])) {
                        $product['name'] = $product['min_variant_name'];
                    }
                    if (!empty($product['min_variant_image'])) {
                        $product['image'] = $product['min_variant_image'];
                    }
                }

                // Formatação visual da miniatura
                $product['thumb'] = $imagePresenter->resize($product['image'] ?? '', $config ? (int)$config->get('config_image_product_width') : 228, $config ? (int)$config->get('config_image_product_height') : 228);
                $product['manufacturer_logo_thumb'] = !empty($product['manufacturer_logo']) ? $imagePresenter->resize($product['manufacturer_logo'], 40, 40) : '';

                // Formatação de Preços com Impostos integrados
                if ($currency && $tax && $config) {
                    $priceBase = $tax->calculate($product['price'], $product['tax_class_id'] ?? 0, $config->get('config_tax'));
                    $product['price_formatted'] = $currency->format($priceBase, $currencyCode);

                    $product['special_formatted'] = !empty($product['special']) 
                        ? $currency->format($tax->calculate($product['special'], $product['tax_class_id'] ?? 0, $config->get('config_tax')), $currencyCode) 
                        : false;

                    // Formatação de Preços de Variação
                    $minPrice = !empty($product['min_variant_price']) ? (float)$product['min_variant_price'] : null;
                    $maxPrice = !empty($product['max_variant_price']) ? (float)$product['max_variant_price'] : null;

                    if ($minPrice !== null && $maxPrice !== null && $minPrice !== $maxPrice) {
                        $product['has_variants'] = true;
                        $product['price_min_formatted'] = $currency->format($tax->calculate($minPrice, $product['tax_class_id'] ?? 0, $config->get('config_tax')), $currencyCode);
                        $product['price_max_formatted'] = $currency->format($tax->calculate($maxPrice, $product['tax_class_id'] ?? 0, $config->get('config_tax')), $currencyCode);
                    } else {
                        $product['has_variants'] = false;
                    }
                }
            }
            unset($product);
        }

        // Estrutura de Paginação para a View
        $baseUrlParams = $queryParams;
        unset($baseUrlParams['page'], $baseUrlParams['sort'], $baseUrlParams['order'], $baseUrlParams['limit']);
        
        $buildSearchUrl = function(array $newParams) use ($routeParser, $lang, $baseUrlParams) {
            return $routeParser->urlFor('search', ['lang' => $lang], array_merge($baseUrlParams, $newParams));
        };

        $sorts = [
            ['text' => 'Padrão', 'value' => 'p.sort_order-ASC', 'href' => $buildSearchUrl(['sort' => 'p.sort_order', 'order' => 'ASC'])],
            ['text' => 'Nome (A - Z)', 'value' => 'pd.name-ASC', 'href' => $buildSearchUrl(['sort' => 'pd.name', 'order' => 'ASC'])],
            ['text' => 'Nome (Z - A)', 'value' => 'pd.name-DESC', 'href' => $buildSearchUrl(['sort' => 'pd.name', 'order' => 'DESC'])],
            ['text' => 'Preço (Menor > Maior)', 'value' => 'p.price-ASC', 'href' => $buildSearchUrl(['sort' => 'p.price', 'order' => 'ASC'])],
            ['text' => 'Preço (Maior > Menor)', 'value' => 'p.price-DESC', 'href' => $buildSearchUrl(['sort' => 'p.price', 'order' => 'DESC'])],
        ];

        $limits = [
            ['text' => '12', 'value' => 12, 'href' => $buildSearchUrl(['limit' => 12])],
            ['text' => '24', 'value' => 24, 'href' => $buildSearchUrl(['limit' => 24])],
            ['text' => '48', 'value' => 48, 'href' => $buildSearchUrl(['limit' => 48])],
            ['text' => '96', 'value' => 96, 'href' => $buildSearchUrl(['limit' => 96])],
        ];

        $data['sorts'] = $sorts;
        $data['limits'] = $limits;
        $data['pagination'] = [
            'page' => $filterData['page'],
            'url'  => str_replace('%7Bpage%7D', '{page}', $buildSearchUrl([
                'page'  => '{page}',
                'sort'  => $filterData['sort'],
                'order' => $filterData['order'],
                'limit' => $filterData['limit']
            ]))
        ];

        // SEO tags
        $seoData = [
            'title'       => 'Resultado de Busca: ' . ($filterData['filter_name'] ?: 'Todos os Produtos') . ' | meusite',
            'description' => 'Resultado da pesquisa por produtos artesanais na meusite.',
            'canonical'   => $routeParser->urlFor('search', ['lang' => $lang])
        ];

        // Preparar listas para a barra lateral de filtros facetados na busca
        $listaCategorias = $this->categoryRepository->getCategories(0);
        foreach ($listaCategorias as &$cat) {
            $catId = (int)($cat['id'] ?? 0);
            $keyword = $this->seoRepository->getKeywordByQuery('category_id', $catId, 0, $languageId);
            $catSlug = !empty($keyword) ? $keyword : $catId;
            $cat['href'] = $routeParser->urlFor('category.detail', ['lang' => $lang, 'slug' => (string)$catSlug]);
            $cat['thumb'] = $imagePresenter->resize($cat['image'] ?? '', $config ? (int)$config->get('config_image_category_width') : 80, $config ? (int)$config->get('config_image_category_height') : 80);
        }
        unset($cat);

        $listaManufacturers = $this->manufacturerRepository->getManufacturers([]);

        $filtrosAtivos = [
            'category'     => $filterData['filter_categories'],
            'manufacturer' => $filterData['filter_manufacturers'],
            'price_min'    => $filterData['filter_price_min'],
            'price_max'    => $filterData['filter_price_max'],
            'rating'       => $filterData['filter_rating']
        ];

        $html = $this->twig->render('pages/product/search.html.twig', [
            'search_data'         => $data,
            'seo'                 => $seoData,
            'term'                => $filterData['filter_name'],
            'title'               => $seoData['title'],
            'description'         => $seoData['description'],
            'lista_categorias'    => $listaCategorias,
            'lista_manufacturers' => $listaManufacturers,
            'filtros_ativos'      => $filtrosAtivos,
            'sorts'               => $sorts,
            'limits'              => $limits,
            'lang'                => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
