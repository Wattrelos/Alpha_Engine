<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListCategoriesAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $limit = 15;

        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->getRepository(CategoryRepository::class);

        // Busca dados paginados e filtrados via Repositório de Domínio
        $result = $categoryRepository->getCategoriesPaginated($queryParams, $page, $limit, $this->languageId);
        $totalCategories = $result['total'];
        $categoriesData = $result['data'];

        $imagePresenter = $this->getImagePresenter();
        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[] = [
                'category_id' => $cat['id'] ?? 0,
                'name'        => $cat['name'] ?? '',
                'parent'      => $cat['parent_name'] ?? 'Raiz',
                'sort_order'  => $cat['sort_order'] ?? 0,
                'status'      => $cat['status'] ? 'Ativo' : 'Inativo',
                'image'       => $cat['image'] ? $imagePresenter->resize($cat['image'], 40, 40, false) : ''
            ];
        }

        // Reconstrói URL de paginação preservando os filtros ativos
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.category.list');
        } catch (\Throwable $e) {
            $baseUrl = (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/categorias';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/pages/category/list.html.twig', [
            'title'        => 'Categorias | Painel Administrativo',
            'categories'   => $categories,
            'total'        => $totalCategories,
            'limit'        => $limit,
            'current_page' => $page,
            'url'          => $url,
            'filters'      => $queryParams
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

