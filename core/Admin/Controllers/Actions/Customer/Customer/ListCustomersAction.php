<?php

namespace Alpha\Admin\Controllers\Actions\Customer\Customer;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

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
        $start = ($page - 1) * $limit;

        $dao = new DataAccessObject();

        // 1. Processa Filtros (QueryBuilder de Contagem)
        $countBuilder = (new QueryBuilder())
            ->from(DB_PREFIX . 'customer', 'c');

        // Processa Filtros (QueryBuilder de Dados)
        $dataBuilder = (new QueryBuilder())
            ->from(DB_PREFIX . 'customer', 'c')
            ->select(
                'c.id', 'c.firstname', 'c.lastname', 'c.email', 'c.telephone', 'c.status', 'c.date_added',
                "(SELECT name FROM `" . DB_PREFIX . "customer_group_description` cgd WHERE cgd.customer_group_id = c.customer_group_id AND cgd.language_id = " . (int)$this->languageId . " LIMIT 1) AS customer_group"
            );

        if (!empty($queryParams['filter_name'])) {
            $countBuilder->where("CONCAT(c.firstname, ' ', c.lastname) LIKE ?", ["%" . $queryParams['filter_name'] . "%"]);
            $dataBuilder->where("CONCAT(c.firstname, ' ', c.lastname) LIKE ?", ["%" . $queryParams['filter_name'] . "%"]);
        }

        if (!empty($queryParams['filter_email'])) {
            $countBuilder->where("c.email LIKE ?", ["%" . $queryParams['filter_email'] . "%"]);
            $dataBuilder->where("c.email LIKE ?", ["%" . $queryParams['filter_email'] . "%"]);
        }

        if (!empty($queryParams['filter_customer_group_id'])) {
            $countBuilder->where("c.customer_group_id = ?", [(int)$queryParams['filter_customer_group_id']]);
            $dataBuilder->where("c.customer_group_id = ?", [(int)$queryParams['filter_customer_group_id']]);
        }

        if (isset($queryParams['filter_status']) && $queryParams['filter_status'] !== '') {
            $countBuilder->where("c.status = ?", [(int)$queryParams['filter_status']]);
            $dataBuilder->where("c.status = ?", [(int)$queryParams['filter_status']]);
        }

        // 2. Executa Contagem Total
        $totalCustomers = $dao->executeCount($countBuilder);

        // 3. Executa Query de Dados Paginados
        $dataBuilder->orderBy('c.date_added', 'DESC')
            ->orderBy('c.firstname', 'ASC')
            ->limit($limit)
            ->offset($start);
        
        $customersData = $dao->executeQuery($dataBuilder);

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

        // Lista de grupos de clientes para o filtro usando QueryBuilder
        $groupBuilder = (new QueryBuilder())
            ->from(DB_PREFIX . 'customer_group', 'cg')
            ->join(DB_PREFIX . 'customer_group_description', 'cgd', 'cg.id = cgd.customer_group_id')
            ->select('cg.id', 'cgd.name')
            ->where('cgd.language_id = ?', [$this->languageId])
            ->orderBy('cg.sort_order', 'ASC');
        
        $customerGroups = $dao->executeQuery($groupBuilder);

        // Reconstrói URL de paginação preservando os filtros ativos
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.customer.list');
        } catch (\Throwable $e) {
            $baseUrl = '/LPDHED2dC7Gjrg2b/clientes';
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
