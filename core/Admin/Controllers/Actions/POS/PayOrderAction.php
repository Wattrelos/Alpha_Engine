<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\POS;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Exception;

/**
 * Action responsável por finalizar o pagamento da pré-venda no Caixa do PDV.
 */
class PayOrderAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $orderId = isset($args['id']) ? (int)$args['id'] : 0;
        
        $body = (string)$request->getBody();
        $data = json_decode($body, true) ?? [];

        $paymentMethod = $data['payment_method'] ?? 'Pix';

        try {
            /** @var OrderRepository $orderRepo */
            $orderRepo = $this->getRepository(OrderRepository::class);
            $order = $orderRepo->getOrder($orderId);

            if (empty($order)) {
                throw new Exception('Pedido não encontrado.');
            }

            if ((int)$order['order_status_id'] !== 1) {
                throw new Exception('Este pedido não está pendente para pagamento.');
            }

            $uow = new UnitOfWork();

            // Transiciona o status do pedido para "Completo" (ID 5) de forma atômica
            $uow->transaction(function() use ($orderRepo, $orderId, $paymentMethod) {
                $orderRepo->confirm($orderId, 5, 'Pagamento processado no PDV Caixa via ' . $paymentMethod);
            });

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Pagamento concluído e pedido finalizado com sucesso.'
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error'   => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
