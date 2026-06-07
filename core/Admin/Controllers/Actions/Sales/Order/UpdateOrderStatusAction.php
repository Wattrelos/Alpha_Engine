<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Order;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderRepository;

class UpdateOrderStatusAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $orderId = (int)($args['id'] ?? 0);
        $postData = $request->getParsedBody();

        $orderStatusId = (int)($postData['order_status_id'] ?? 0);
        $comment = (string)($postData['comment'] ?? '');
        $notify = isset($postData['notify']) && $postData['notify'] == '1';

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->getRepository(OrderRepository::class);
        $order = $orderRepo->getOrder($orderId);

        if (!$order) {
            $response->getBody()->write("Pedido não encontrado.");
            return $response->withStatus(404);
        }

        if ($orderStatusId > 0) {
            $orderRepo->confirm($orderId, $orderStatusId, $comment, $notify);
        }

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $redirectUrl = $routeContext->getRouteParser()->urlFor('admin.orders.show', ['id' => $orderId]);
        } catch (\Throwable $e) {
            $redirectUrl = '/LPDHED2dC7Gjrg2b/pedidos/' . $orderId;
        }

        return $response->withHeader('Location', $redirectUrl . '?success=1')->withStatus(302);
    }
}
