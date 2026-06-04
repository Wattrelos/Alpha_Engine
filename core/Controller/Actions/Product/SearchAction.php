<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;
use Alpha\Controller\Actions\ActionInterface;

class SearchAction implements ActionInterface
{
    private ProductRepository $productRepository;
    private SeoUrlRepository $seoRepository;
    private TwigEnvironment $twig;
    private \Alpha\Support\Language $translator;

    public function __construct(
        ProductRepository $productRepository,
        SeoUrlRepository $seoRepository,
        TwigEnvironment $twig,
        \Alpha\Support\Language $translator
    ) {
        $this->productRepository = $productRepository;
        $this->seoRepository = $seoRepository;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->translator->load('product/search');
        $this->twig->addGlobal('Search', $this->translator->getNestedData('product/search'));

        $queryParams = $request->getQueryParams();
        
        $filterData = array_merge($queryParams, [
            'search'             => $queryParams['busca'] ?? $queryParams['search'] ?? '',
            'filter_name'        => $queryParams['busca'] ?? $queryParams['search'] ?? '',
            'filter_description' => $queryParams['description'] ?? '',
            'filter_category_id' => (int)($queryParams['category_id'] ?? 0),
            'filter_sub_category'=> $queryParams['sub_category'] ?? '',
            'sort'               => $queryParams['sort'] ?? 'p.sort_order',
            'order'              => $queryParams['order'] ?? 'ASC',
            'page'               => max(1, (int)($queryParams['page'] ?? 1)),
            'limit'              => max(1, (int)($queryParams['limit'] ?? 12))
        ]);

        // Obtém dados de busca do repositório
        $viewResponse = $this->productRepository->getSearchData($filterData);
        $data = $viewResponse->getData();

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');
        $languageId = $request->getAttribute('language_id', 2);

        // Garante que o href e o slug dos produtos da busca sejam construídos
        // de forma inteligente utilizando o roteador de URLs amigáveis do Slim.
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
            }
            unset($product);
        }

        // Estrutura de Paginação para a View
        $baseUrlParams = $queryParams;
        unset($baseUrlParams['page'], $baseUrlParams['sort'], $baseUrlParams['order'], $baseUrlParams['limit']);
        
        $buildSearchUrl = function(array $newParams) use ($routeParser, $lang, $baseUrlParams) {
            return $routeParser->urlFor('search', ['lang' => $lang], array_merge($baseUrlParams, $newParams));
        };

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
            'title'       => 'Resultado de Busca: ' . ($filterData['filter_name'] ?: 'Todos os Produtos') . ' | AgSonhos',
            'description' => 'Resultado da pesquisa por produtos artesanais na AgSonhos.',
            'canonical'   => $routeParser->urlFor('search', ['lang' => $lang])
        ];

        $html = $this->twig->render('pages/product/search.html.twig', [
            'search_data' => $data,
            'seo'         => $seoData,
            'term'        => $filterData['filter_name'],
            'title'       => $seoData['title'],
            'description' => $seoData['description'],
            'lang'        => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
