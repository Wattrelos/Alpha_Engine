<?php

namespace Alpha\Admin\Controllers\Actions\Procurement\Supplier;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\SupplierRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

class ListSuppliersAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 15;

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = RepositoryFactory::getInstance()->get(SupplierRepository::class);
        
        $filters = [
            'page'           => $page,
            'limit'          => $limit,
            'filter_name'    => $queryParams['filter_name'] ?? '',
            'filter_tax_id'  => $queryParams['filter_tax_id'] ?? ''
        ];

        $indexData = $supplierRepository->getIndexData($filters);

        $suppliers = [];
        foreach ($indexData['data'] as $s) {
            $suppliers[] = [
                'id'           => $s->getId(),
                'company_name' => $s->getCompanyName(),
                'trade_name'   => $s->getTradeName(),
                'tax_id'       => $s->getTaxId(),
                'email'        => $s->getEmail(),
                'phone'        => $s->getPhone(),
                'is_active'    => $s->getIsActive()
            ];
        }

        // Reconstrói URL de paginação preservando os filtros ativos
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.supplier.list');
        } catch (\Throwable $e) {
            $baseUrl = (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/fornecedores';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/catalog/supplier/index.html.twig', [
            'title'        => 'Fornecedores | Painel Administrativo',
            'suppliers'    => $suppliers,
            'total'        => $indexData['total'],
            'limit'        => $limit,
            'current_page' => $page,
            'url'          => $url,
            'filters'      => $queryParams
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
