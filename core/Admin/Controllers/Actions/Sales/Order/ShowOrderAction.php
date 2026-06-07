<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Order;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\OrderStatusRepository;

class ShowOrderAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $orderId = (int)($args['id'] ?? 0);

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->getRepository(OrderRepository::class);
        $order = $orderRepo->getOrder($orderId);

        if (!$order) {
            $response->getBody()->write("Pedido não encontrado.");
            return $response->withStatus(404);
        }

        // Fetch products, options, totals and history
        $productsData = $orderRepo->getProducts($orderId);
        $products = [];
        foreach ($productsData as $product) {
            $options = $orderRepo->getOptions($orderId, (int)$product['order_product_id']);
            $products[] = [
                'name'     => $product['name'],
                'model'    => $product['model'],
                'quantity' => $product['quantity'],
                'price'    => 'R$ ' . number_format((float)$product['price'], 2, ',', '.'),
                'total'    => 'R$ ' . number_format((float)$product['total'], 2, ',', '.'),
                'options'  => $options,
            ];
        }

        $totals = $orderRepo->getTotals($orderId);
        $histories = $orderRepo->getHistories($orderId);

        // Fetch order statuses for status update dropdown
        /** @var OrderStatusRepository $statusRepo */
        $statusRepo = $this->getRepository(OrderStatusRepository::class);
        $statuses = $statusRepo->getOrderStatuses();

        // Success / error message handling
        $queryParams = $request->getQueryParams();
        $success = $queryParams['success'] ?? null;

        $html = $this->getTemplate('admin/sales/order/show.html.twig', [
            'title'     => 'Pedido #' . $orderId . ' | Painel Administrativo',
            'order'     => $order,
            'products'  => $products,
            'totals'    => $totals,
            'histories' => $histories,
            'statuses'  => $statuses,
            'success'   => $success,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
