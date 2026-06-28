<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\POS;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderRepository;

/**
 * Action responsável por carregar os detalhes de uma pré-venda (pedido) no caixa.
 */
class GetPreOrderAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $orderId = isset($args['id']) ? (int)$args['id'] : 0;

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->getRepository(OrderRepository::class);
        $order = $orderRepo->getOrder($orderId);

        if (empty($order)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error'   => 'ORDER_NOT_FOUND',
                'message' => 'Pré-venda/Pedido não encontrado.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $productsRaw = $orderRepo->getProducts($orderId);
        $products = [];
        foreach ($productsRaw as $p) {
            $products[] = [
                'name'      => $p['name'],
                'model'     => $p['model'] ?? '',
                'quantity'  => (int)$p['quantity'],
                'price'     => (float)$p['price'],
                'total'     => (float)$p['total'],
            ];
        }

        $currency = $this->container->has('currency') ? $this->container->get('currency') : null;
        $config = $this->container->has('config') ? $this->container->get('config') : null;
        $currencyCode = $config ? $config->get('config_currency') : 'BRL';

        $totalFormatted = $currency ? $currency->format((float)$order['total'], $currencyCode) : 'R$ ' . number_format((float)$order['total'], 2, ',', '.');

        $orderData = [
            'success'          => true,
            'order_id'         => (int)$order['order_id'],
            'customer_name'    => $order['firstname'] . ' ' . $order['lastname'],
            'email'            => $order['email'],
            'telephone'        => $order['telephone'],
            'order_status_id'  => (int)$order['order_status_id'],
            'total'            => (float)$order['total'],
            'total_formatted'  => $totalFormatted,
            'products'         => $products,
        ];

        $response->getBody()->write(json_encode($orderData, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
