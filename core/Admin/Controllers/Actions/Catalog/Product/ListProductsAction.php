<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListProductsAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(ProductRepository::class);
        /** @var CategoryRepository $categoryRepo */
        $categoryRepo = $this->getRepository(CategoryRepository::class);
        /** @var ManufacturerRepository $manufacturerRepo */
        $manufacturerRepo = $this->getRepository(ManufacturerRepository::class);
        
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $limit = 15;

        // 1. Busca produtos paginados e filtrados via Repositório de Domínio
        $result = $productRepo->getAdminProductsPaginated($queryParams, $page, $limit, $this->languageId);
        $totalProducts = $result['total'];
        $productsData = $result['data'];

        // 2. Carrega listas auxiliares para os selects via repositórios
        $categories = $categoryRepo->getCategoriesForSelect($this->languageId);
        $manufacturers = $manufacturerRepo->getManufacturers();

        $imagePresenter = $this->getImagePresenter();
        $products = [];
        foreach ($productsData as $prod) {
            $minPrice = $prod['min_variant_price'] !== null ? (float)$prod['min_variant_price'] : null;
            $maxPrice = $prod['max_variant_price'] !== null ? (float)$prod['max_variant_price'] : null;

            if ($minPrice !== null && $maxPrice !== null && $minPrice !== $maxPrice) {
                $priceDisplay = 'R$ ' . number_format($minPrice, 2, ',', '.') . ' - R$ ' . number_format($maxPrice, 2, ',', '.');
            } else {
                $priceDisplay = 'R$ ' . number_format((float)($prod['price'] ?? 0), 2, ',', '.');
            }

            $products[] = [
                'product_id' => $prod['id'] ?? 0,
                'name'       => $prod['name'] ?? '',
                'model'      => $prod['model'] ?? '',
                'price'      => $priceDisplay,
                'quantity'   => $prod['quantity'] ?? 0,
                'status'     => $prod['status'] ? 'Ativo' : 'Inativo',
                'image'      => $imagePresenter->resize($prod['image'] ?? '', 40, 40, false)
            ];
        }

        // Reconstrói URL de paginação preservando os filtros ativos
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.product.list');
        } catch (\Throwable $e) {
            $baseUrl = (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/produtos';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/pages/products/list.html.twig', [
            'title'          => 'Produtos | Painel Administrativo',
            'products'       => $products,
            'total'          => $totalProducts,
            'limit'          => $limit,
            'current_page'   => $page,
            'url'            => $url,
            'categories'     => $categories,
            'manufacturers'  => $manufacturers,
            'filters'        => $queryParams,
            'success'        => $queryParams['success'] ?? null,
            'error'          => $queryParams['error'] ?? null
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

