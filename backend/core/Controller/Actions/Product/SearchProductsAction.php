<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Twig\Environment as TwigEnvironment;
use Alpha\Support\Language as Translator;
use Slim\Routing\RouteContext;

class SearchProductsAction
{
    private ProductRepository $productRepository;
    private TwigEnvironment $twig;
    private Translator $translator;

    public function __construct(ProductRepository $productRepository, TwigEnvironment $twig, Translator $translator)
    {
        $this->productRepository = $productRepository;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Carrega traduções de busca
        $this->translator->load('product/search');
        $this->twig->addGlobal('Search', $this->translator->getNestedData('product/search'));

        $queryParams = $request->getQueryParams();
        
        $filterData = [
            'filter_name'        => $queryParams['busca'] ?? $queryParams['search'] ?? '',
            'filter_description' => $queryParams['description'] ?? '',
            'filter_category_id' => (int)($queryParams['category_id'] ?? 0),
            'filter_sub_category'=> $queryParams['sub_category'] ?? '',
            'sort'               => $queryParams['sort'] ?? 'p.sort_order',
            'order'              => $queryParams['order'] ?? 'ASC',
            'page'               => max(1, (int)($queryParams['page'] ?? 1)),
            'limit'              => max(1, (int)($queryParams['limit'] ?? 12))
        ];

        // Obtém dados de busca do repositório
        $viewResponse = $this->productRepository->getSearchData($filterData);
        $data = $viewResponse->getData();

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        // Construção dos menus de Ordenação e Exibição para o Twig
        $baseUrlParams = $queryParams;
        unset($baseUrlParams['page'], $baseUrlParams['sort'], $baseUrlParams['order'], $baseUrlParams['limit']);
        
        $buildUrl = function(array $newParams) use ($routeParser, $lang, $baseUrlParams) {
            return $routeParser->urlFor('search', ['lang' => $lang], array_merge($baseUrlParams, $newParams));
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

        $data['sorts'] = $sorts;
        $data['limits'] = $limits;
        $data['pagination'] = [
            'page' => $filterData['page'],
            'url'  => str_replace('%7Bpage%7D', '{page}', $buildUrl([
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
            'description' => $seoData['description']
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}

