<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Order;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\OrderStatusRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->getRepository(OrderRepository::class);

        // Busca a listagem paginada e filtrada via Repositório de Domínio
        $result = $orderRepo->getAdminOrdersPaginated($queryParams, $page, $limit, $this->languageId);
        $totalOrders = $result['total'];
        $ordersData = $result['data'];

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

        // Carrega status para o filtro select
        /** @var OrderStatusRepository $statusRepo */
        $statusRepo = $this->getRepository(OrderStatusRepository::class);
        $statuses = $statusRepo->getOrderStatuses();

        // Reconstrução de URL de paginação
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.orders.index');
        } catch (\Throwable $e) {
            $baseUrl = (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/pedidos';
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

