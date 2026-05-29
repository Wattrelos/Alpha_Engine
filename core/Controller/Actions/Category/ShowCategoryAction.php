<?php

namespace Alpha\Controller\Actions\Category;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;

class ShowCategoryAction implements ActionInterface
{
    private CategoryRepository $categoryRepository;
    private SeoUrlRepository $seoRepository;
    private TwigEnvironment $twig;

    public function __construct(
        CategoryRepository $categoryRepository,
        SeoUrlRepository $seoRepository,
        TwigEnvironment $twig
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->seoRepository = $seoRepository;
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
            'limit'         => (int)($queryParams['limit'] ?? 12)
        ];

        // Obtém os dados consolidados da categoria e seus produtos
        $viewResponse = $this->categoryRepository->getCategoryData($categoryId, $filterData);
        $data = $viewResponse->getData();

        if (empty($data)) {
            return $this->render404($response);
        }

        // SEO tags
        $seoData = [
            'title'       => ($data['meta_title'] ?? $data['name']) . ' | AgSonhos',
            'description' => $data['meta_description'] ?? 'Confira nossa categoria de produtos.',
            'keywords'    => $data['meta_keyword'] ?? '',
            'image'       => $data['thumb'] ?? '',
            'canonical'   => '/' . $request->getAttribute('language_code', 'pt-br') . '/categoria/' . $slug
        ];

        $html = $this->twig->render('pages/category/show.html.twig', [
            'category'    => $data,
            'seo'         => $seoData,
            'title'       => $seoData['title'],
            'description' => $seoData['description'],
            'keywords'    => $seoData['keywords']
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
