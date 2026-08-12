<?php

namespace Alpha\Controller\Actions\Category;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;
use Psr\Container\ContainerInterface;
use Alpha\Support\Presenters\ImagePresenter;

class ShowCategoryAction implements ActionInterface
{
    private CategoryRepository $categoryRepository;
    private SeoUrlRepository $seoRepository;
    private ManufacturerRepository $manufacturerRepository;
    private TwigEnvironment $twig;
    private ContainerInterface $container;
    private ImagePresenter $imagePresenter;

    public function __construct(
        CategoryRepository $categoryRepository,
        SeoUrlRepository $seoRepository,
        ManufacturerRepository $manufacturerRepository,
        TwigEnvironment $twig,
        ContainerInterface $container,
        ImagePresenter $imagePresenter
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->seoRepository = $seoRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->twig = $twig;
        $this->container = $container;
        $this->imagePresenter = $imagePresenter;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'] ?? '';
        $languageId = $request->getAttribute('language_id', 2);
        $categoryId = 0;

        // Se o slug for numérico, assume que é o ID diretamente
        if (is_numeric($slug)) {
            $categoryId = (int)$slug;
        } else {
            // Resolve via seo_url
            $queryStr = $this->seoRepository->getQueryByKeyword($slug, 0, $languageId);
            if (!empty($queryStr)) {
                parse_str($queryStr, $resolvedParams);
                if (isset($resolvedParams['category_id'])) {
                    $categoryId = (int)$resolvedParams['category_id'];
                }
            }
        }

        if ($categoryId <= 0) {
            return $this->render404($response);
        }

        // Filtros e paginação da query string
        $queryParams = $request->getQueryParams();
        $filterData = [
            'path'          => $slug,
            'filter_filter' => $queryParams['filter'] ?? null,
            'sort'          => $queryParams['sort'] ?? 'p.sort_order',
            'order'         => $queryParams['order'] ?? 'ASC',
            'page'          => max(1, (int)($queryParams['page'] ?? 1)),
            'limit'         => max(1, (int)($queryParams['limit'] ?? 12)),
            
            // Filtros facetados
            'categories'    => $queryParams['category'] ?? [],
            'manufacturers' => $queryParams['manufacturer'] ?? [],
            'price_min'     => $queryParams['price_min'] ?? null,
            'price_max'     => $queryParams['price_max'] ?? null,
            'rating'        => $queryParams['rating'] ?? null
        ];

        // Obtém os dados consolidados da categoria e seus produtos
        $viewResponse = $this->categoryRepository->getCategoryData($categoryId, $filterData);
        $data = $viewResponse->getData();

        if (empty($data)) {
            return $this->render404($response);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $config = $this->container->has('config') ? $this->container->get('config') : null;
        $currency = $this->container->has('currency') ? $this->container->get('currency') : null;
        $tax = $this->container->has('tax') ? $this->container->get('tax') : null;
        $session = $this->container->has('session') ? $this->container->get('session') : null;
        $currencyCode = $session->data['currency'] ?? ($config ? $config->get('config_currency') : 'BRL');
        $imagePresenter = $this->imagePresenter;
        
        // Imagem principal da categoria via Presenter
        $data['thumb'] = !empty($data['image']) ? $imagePresenter->resize($data['image'], $config ? (int)$config->get('config_image_category_width') : 870, $config ? (int)$config->get('config_image_category_height') : 330) : '';

        // Orquestração de Breadcrumbs com geração de URLs físicas (Slim Router)
        $breadcrumbs = [];
        $breadcrumbs[] = ['text' => 'Home', 'href' => $routeParser->urlFor('home', ['lang' => $lang])];
        if (!empty($data['breadcrumbs'])) {
            foreach ($data['breadcrumbs'] as $crumb) {
                $crumbId = (int)($crumb['id'] ?? 0);
                $keyword = $this->seoRepository->getKeywordByQuery('category_id', $crumbId, 0, $languageId);
                $breadcrumbs[] = [
                    'text' => $crumb['name'],
                    'href' => $routeParser->urlFor('category.detail', ['lang' => $lang, 'slug' => (string)(!empty($keyword) ? $keyword : $crumbId)])
                ];
            }
        }

        // Mapeamento inteligente de URLs amigáveis (SEO) para os produtos da categoria
        if (isset($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as &$product) {
                $prodId = (int)($product['product_id'] ?? $product['id'] ?? 0);

                // Normaliza a chave para que o template acesse prod.product_id independente do nome da coluna no BD
                if (!isset($product['product_id']) && isset($product['id'])) {
                    $product['product_id'] = $product['id'];
                }

                $keyword = $prodId > 0 ? $this->seoRepository->getKeywordByQuery('product_id', $prodId, 0, $languageId) : '';
                
                $productSlug = !empty($keyword) ? $keyword : (!empty($product['keyword']) ? $product['keyword'] : $prodId);
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

        // SEO tags
        $seoData = [
            'title'       => ($data['meta_title'] ?? $data['name']) . ' | AgSonhos',
            'description' => $data['meta_description'] ?? 'Confira nossa categoria de produtos.',
            'keywords'    => $data['meta_keyword'] ?? '',
            'image'       => $data['thumb'] ?? '',
            'canonical'   => $routeParser->urlFor('category.detail', ['lang' => $lang, 'slug' => $slug])
        ];

        // Preparar listas para a barra lateral de filtros facetados
        // Se a categoria atual tiver subcategorias, mostramos as subcategorias dela.
        // Se não tiver (folha), mostramos as subcategorias da categoria pai (suas "irmãs").
        $listaCategorias = $this->categoryRepository->getCategories($categoryId);
        if (empty($listaCategorias)) {
            $parentId = (int)($data['parent_id'] ?? 0);
            $listaCategorias = $this->categoryRepository->getCategories($parentId);
        }

        // Orquestração visual das Subcategorias (Imagens e Links)
        foreach ($listaCategorias as &$cat) {
            $catId = (int)($cat['id'] ?? 0);
            $keyword = $this->seoRepository->getKeywordByQuery('category_id', $catId, 0, $languageId);
            $catSlug = !empty($keyword) ? $keyword : $catId;
            $cat['href'] = $routeParser->urlFor('category.detail', ['lang' => $lang, 'slug' => (string)$catSlug]);
            $cat['thumb'] = $imagePresenter->resize($cat['image'] ?? '', $config ? (int)$config->get('config_image_category_width') : 80, $config ? (int)$config->get('config_image_category_height') : 80);
        }
        unset($cat);

        // Construção dos menus de Ordenação e Exibição para o Twig
        $baseUrlParams = $queryParams;
        unset($baseUrlParams['page'], $baseUrlParams['sort'], $baseUrlParams['order'], $baseUrlParams['limit']);
        
        $buildUrl = function(array $newParams) use ($routeParser, $lang, $slug, $baseUrlParams) {
            return $routeParser->urlFor('category.detail', ['lang' => $lang, 'slug' => $slug], array_merge($baseUrlParams, $newParams));
        };

        $sorts = [
            ['text' => 'Padrão', 'value' => 'p.sort_order-ASC', 'href' => $buildUrl(['sort' => 'p.sort_order', 'order' => 'ASC'])],
            ['text' => 'Nome (A - Z)', 'value' => 'pd.name-ASC', 'href' => $buildUrl(['sort' => 'pd.name', 'order' => 'ASC'])],
            ['text' => 'Nome (Z - A)', 'value' => 'pd.name-DESC', 'href' => $buildUrl(['sort' => 'pd.name', 'order' => 'DESC'])],
            ['text' => 'Preço (Menor > Maior)', 'value' => 'p.price-ASC', 'href' => $buildUrl(['sort' => 'p.price', 'order' => 'ASC'])],
            ['text' => 'Preço (Maior > Menor)', 'value' => 'p.price-DESC', 'href' => $buildUrl(['sort' => 'p.price', 'order' => 'DESC'])],
        ];

        $limits = [
            ['text' => '12', 'value' => 12, 'href' => $buildUrl(['limit' => 12])],
            ['text' => '24', 'value' => 24, 'href' => $buildUrl(['limit' => 24])],
            ['text' => '48', 'value' => 48, 'href' => $buildUrl(['limit' => 48])],
            ['text' => '96', 'value' => 96, 'href' => $buildUrl(['limit' => 96])],
        ];
        
        // Carrega marcas dinamicamente que possuam produtos na categoria atual
        $listaManufacturers = $this->manufacturerRepository->getManufacturersByCategory($categoryId);

        $filtrosAtivos = [
            'category'     => $filterData['categories'],
            'manufacturer' => $filterData['manufacturers'],
            'price_min'    => $filterData['price_min'],
            'price_max'    => $filterData['price_max'],
            'rating'       => $filterData['rating']
        ];

        $data['pagination'] = [
            'page' => $filterData['page'],
            'url'  => str_replace('%7Bpage%7D', '{page}', $buildUrl([
                'page'  => '{page}',
                'sort'  => $filterData['sort'],
                'order' => $filterData['order'],
                'limit' => $filterData['limit']
            ]))
        ];
        
        $data['sorts'] = $sorts;
        $data['limits'] = $limits;

        $html = $this->twig->render('pages/category/show.html.twig', [
            'category'           => $data,
            'breadcrumbs'        => $breadcrumbs,
            'seo'                => $seoData,
            'title'              => $seoData['title'],
            'description'        => $seoData['description'],
            'keywords'           => $seoData['keywords'],
            'lista_categorias'   => $listaCategorias,
            'lista_manufacturers'=> $listaManufacturers,
            'filtros_ativos'     => $filtrosAtivos,
            'sorts'              => $sorts,
            'limits'             => $limits,
            'lang'               => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    private function render404(Response $response): Response
    {
        $html404 = $this->twig->render('pages/errors/404.html.twig', [
            'title'       => 'Categoria Não Encontrada | AgSonhos',
            'description' => 'A categoria solicitada não foi encontrada.',
        ]);
        $response->getBody()->write($html404);
        return $response->withStatus(404);
    }
}
