<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Order;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderRepository;

class ViewOrderDetailsAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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

        // Fetch products and totals
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

        // Fetch store settings for invoice header
        $settings = $this->container->has('configSettings') ? $this->container->get('configSettings') : [];

        $html = $this->getTemplate('admin/sales/order/invoice.html.twig', [
            'title'     => 'Fatura do Pedido #' . $orderId,
            'order'     => $order,
            'products'  => $products,
            'totals'    => $totals,
            'store'     => [
                'name'      => $settings['config_name'] ?? 'Sonhos de Ninar',
                'address'   => $settings['config_address'] ?? '',
                'telephone' => $settings['config_telephone'] ?? '',
                'email'     => $settings['config_email'] ?? '',
                'url'       => $settings['config_url'] ?? HTTP_SERVER,
            ]
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
