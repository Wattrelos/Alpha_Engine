<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Order;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\OrderStatusRepository;

class ListOrdersAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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

        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();

        $where = [];
        $params = [];

        // Filter by Order ID
        if (!empty($queryParams['filter_order_id'])) {
            $where[] = "o.id = ?";
            $params[] = (int)$queryParams['filter_order_id'];
        }

        // Filter by Customer
        if (!empty($queryParams['filter_customer'])) {
            $where[] = "CONCAT(o.firstname, ' ', o.lastname) LIKE ?";
            $params[] = "%" . $queryParams['filter_customer'] . "%";
        }

        // Filter by Status
        if (!empty($queryParams['filter_order_status_id'])) {
            $where[] = "o.order_status_id = ?";
            $params[] = (int)$queryParams['filter_order_status_id'];
        }

        // Filter by Total
        if (!empty($queryParams['filter_total'])) {
            $where[] = "o.total = ?";
            $params[] = (float)$queryParams['filter_total'];
        }

        // Filter by Date Added
        if (!empty($queryParams['filter_date_added'])) {
            $where[] = "DATE(o.date_added) = ?";
            $params[] = $queryParams['filter_date_added'];
        }

        // Filter by Date Modified
        if (!empty($queryParams['filter_date_modified'])) {
            $where[] = "DATE(o.date_modified) = ?";
            $params[] = $queryParams['filter_date_modified'];
        }

        $whereSql = '';
        if ($where) {
            $whereSql = "WHERE " . implode(" AND ", $where);
        }

        // Query Total Count
        $countQuery = "
            SELECT COUNT(o.id)
            FROM `" . DB_PREFIX . "order` o
            $whereSql
        ";
        $stmtCount = $conn->prepare($countQuery);
        $stmtCount->execute($params);
        $totalOrders = (int)$stmtCount->fetchColumn();

        // Query Data
        $dataQuery = "
            SELECT o.id, o.firstname, o.lastname, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified, os.name AS status_name
            FROM `" . DB_PREFIX . "order` o
            LEFT JOIN `" . DB_PREFIX . "order_status` os ON o.order_status_id = os.id AND os.language_id = ?
            $whereSql
            ORDER BY o.id DESC
            LIMIT " . (int)$limit . " OFFSET " . (int)$start;
        
        $stmtData = $conn->prepare($dataQuery);
        $stmtData->execute(array_merge([$this->languageId], $params));
        $ordersData = $stmtData->fetchAll(\PDO::FETCH_ASSOC);

        $orders = [];
        foreach ($ordersData as $o) {
            $orders[] = [
                'order_id'      => $o['id'],
                'customer'      => $o['firstname'] . ' ' . $o['lastname'],
                'status'        => $o['status_name'] ?? 'Pendente',
                'total'         => 'R$ ' . number_format((float)$o['total'], 2, ',', '.'),
                'date_added'    => date('d/m/Y H:i', strtotime($o['date_added'])),
                'date_modified' => date('d/m/Y H:i', strtotime($o['date_modified'])),
            ];
        }

        // Load statuses for the select filter
        /** @var OrderStatusRepository $statusRepo */
        $statusRepo = $this->getRepository(OrderStatusRepository::class);
        $statuses = $statusRepo->getOrderStatuses();

        // Pagination URL reconstruction
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.orders.index');
        } catch (\Throwable $e) {
            $baseUrl = '/LPDHED2dC7Gjrg2b/pedidos';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/sales/order/index.html.twig', [
            'title'        => 'Pedidos | Painel Administrativo',
            'orders'       => $orders,
            'total'        => $totalOrders,
            'limit'        => $limit,
            'current_page' => $page,
            'url'          => $url,
            'statuses'     => $statuses,
            'filters'      => $queryParams,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
