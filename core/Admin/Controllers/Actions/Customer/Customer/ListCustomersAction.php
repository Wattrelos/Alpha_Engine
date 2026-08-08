<?php

namespace Alpha\Admin\Controllers\Actions\Customer\Customer;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomerGroupRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListCustomersAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $limit = 15;

        /** @var CustomerRepository $customerRepo */
        $customerRepo = $this->getRepository(CustomerRepository::class);

        // Busca dados paginados e filtrados via repositório de domínio
        $result = $customerRepo->getAdminCustomersPaginated($queryParams, $page, $limit, $this->languageId);
        $totalCustomers = $result['total'];
        $customersData = $result['data'];

        $customers = [];
        foreach ($customersData as $row) {
            $customers[] = [
                'customer_id'    => $row['id'] ?? 0,
                'name'           => ($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''),
                'email'          => $row['email'] ?? '',
                'customer_group' => $row['customer_group'] ?? 'Padrão',
                'status'         => $row['status'] ? 'Ativo' : 'Inativo',
                'date_added'     => !empty($row['date_added']) ? date('d/m/Y H:i', strtotime($row['date_added'])) : ''
            ];
        }

        // Lista de grupos de clientes para o filtro usando o repositório
        /** @var CustomerGroupRepository $groupRepo */
        $groupRepo = $this->getRepository(CustomerGroupRepository::class);
        $customerGroupsRaw = $groupRepo->getCustomerGroups($this->languageId);

        $customerGroups = [];
        foreach ($customerGroupsRaw as $cg) {
            $customerGroups[] = [
                'id'   => $cg['customer_group_id'] ?? $cg['id'] ?? 0,
                'name' => $cg['name'] ?? ''
            ];
        }

        // Reconstrói URL de paginação preservando os filtros ativos
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.customer.list');
        } catch (\Throwable $e) {
            $baseUrl = (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/clientes';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/customer/customer/index.html.twig', [
            'title'           => 'Clientes | Painel Administrativo',
            'customers'       => $customers,
            'customer_groups' => $customerGroups,
            'total'           => $totalCustomers,
            'limit'           => $limit,
            'current_page'    => $page,
            'url'             => $url,
            'filters'         => $queryParams
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

