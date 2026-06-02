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

    public function __construct(
        ProductRepository $productRepository,
        SeoUrlRepository $seoRepository,
        TwigEnvironment $twig
    ) {
        $this->productRepository = $productRepository;
        $this->seoRepository = $seoRepository;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        
        $filterData = array_merge($queryParams, [
            'search'             => $queryParams['busca'] ?? $queryParams['search'] ?? '',
            'filter_name'        => $queryParams['busca'] ?? $queryParams['search'] ?? '',
            'filter_description' => $queryParams['description'] ?? '',
            'filter_category_id' => (int)($queryParams['category_id'] ?? 0),
            'filter_sub_category'=> $queryParams['sub_category'] ?? '',
            'sort'               => $queryParams['sort'] ?? 'p.sort_order',
            'order'              => $queryParams['order'] ?? 'ASC',
            'page'               => (int)($queryParams['page'] ?? 1),
            'limit'              => (int)($queryParams['limit'] ?? 12)
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
                $keyword = $productId > 0 ? $this->seoRepository->getKeywordByQuery('product_id', $productId, 0, $languageId) : '';
                
                $slug = !empty($keyword) ? $keyword : (!empty($product['keyword']) ? $product['keyword'] : $productId);
                $product['slug'] = $slug;
                $product['href'] = $routeParser->urlFor('product.detail', ['lang' => $lang, 'slug' => (string)$slug]);
            }
            unset($product);
        }

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
