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

class ShowCategoryAction implements ActionInterface
{
    private CategoryRepository $categoryRepository;
    private SeoUrlRepository $seoRepository;
    private ManufacturerRepository $manufacturerRepository;
    private TwigEnvironment $twig;

    public function __construct(
        CategoryRepository $categoryRepository,
        SeoUrlRepository $seoRepository,
        ManufacturerRepository $manufacturerRepository,
        TwigEnvironment $twig
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->seoRepository = $seoRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->twig = $twig;
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
            'page'          => (int)($queryParams['page'] ?? 1),
            'limit'         => (int)($queryParams['limit'] ?? 12),
            
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
        
        // Carrega marcas dinamicamente que possuam produtos na categoria atual
        $listaManufacturers = $this->manufacturerRepository->getManufacturersByCategory($categoryId);

        $filtrosAtivos = [
            'category'     => $filterData['categories'],
            'manufacturer' => $filterData['manufacturers'],
            'price_min'    => $filterData['price_min'],
            'price_max'    => $filterData['price_max'],
            'rating'       => $filterData['rating']
        ];

        $html = $this->twig->render('pages/category/show.html.twig', [
            'category'           => $data,
            'seo'                => $seoData,
            'title'              => $seoData['title'],
            'description'        => $seoData['description'],
            'keywords'           => $seoData['keywords'],
            'lista_categorias'   => $listaCategorias,
            'lista_manufacturers'=> $listaManufacturers,
            'filtros_ativos'     => $filtrosAtivos
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
