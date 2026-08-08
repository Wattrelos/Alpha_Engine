<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListManufacturersAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $limit = 15;

        /** @var ManufacturerRepository $manufacturerRepository */
        $manufacturerRepository = $this->getRepository(ManufacturerRepository::class);

        // Busca fabricantes filtrados e paginados via Repositório de Domínio
        $result = $manufacturerRepository->getManufacturersPaginated($queryParams, $page, $limit, $this->storeId);
        $totalManufacturers = $result['total'];
        $manufacturersData = $result['data'];

        $imagePresenter = $this->getImagePresenter();
        $manufacturers = [];
        foreach ($manufacturersData as $m) {
            $manufacturers[] = [
                'manufacturer_id' => $m['id'] ?? 0,
                'name'            => $m['name'] ?? '',
                'sort_order'      => $m['sort_order'] ?? 0,
                'image'           => $m['image'] ? $imagePresenter->resize($m['image'], 40, 40, false) : ''
            ];
        }

        // Reconstrói URL de paginação preservando os filtros ativos
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.manufacturer.list');
        } catch (\Throwable $e) {
            $baseUrl = (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/fabricantes';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/catalog/manufacturer/index.html.twig', [
            'title'         => 'Fabricantes | Painel Administrativo',
            'manufacturers' => $manufacturers,
            'total'         => $totalManufacturers,
            'limit'         => $limit,
            'current_page'  => $page,
            'url'           => $url,
            'filters'       => $queryParams
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

