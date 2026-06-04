<?php

namespace Alpha\Controller\Actions\Checkout;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Services\Payments\PaymentGatewayFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use InvalidArgumentException;

/**
 * Action responsável por processar o fluxo assíncrono de pagamento final no Checkout.
 */
class ProcessPaymentAction implements ActionInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly CartRepository $cartRepository,
        private readonly UnitOfWork $uow,
        private readonly PaymentGatewayFactory $paymentFactory
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $parsedBody = $request->getParsedBody();
            $orderId = $parsedBody['order_id'] ?? null;
            $paymentMethodCode = $parsedBody['payment_method'] ?? null;

            if (!$orderId || !$paymentMethodCode) {
                throw new InvalidArgumentException('Dados de pagamento incompletos. Faltam order_id ou payment_method.');
            }

            // 1. Inicia a transação atômica
            $this->uow->begin();

            // 2. Recupera o Pedido para validação
            $order = $this->orderRepository->getOrder($orderId);
            if (!$order) {
                throw new Exception('Pedido não encontrado para processamento.');
            }

            // 3. Processamento do Gateway (Mock/Abstração da regra de negócio)
            $gateway = $this->paymentFactory->make($paymentMethodCode);
            $paymentResult = $gateway->charge($order);
            if (!$paymentResult->isSuccessful()) {
                throw new Exception('O pagamento foi recusado pela operadora: ' . $paymentResult->getMessage());
            }

            // 4. Efetivação do Pedido (Transiciona do status 0 para 1 - Pendente/Aprovado)
            $defaultOrderStatusId = 1;
            $this->orderRepository->confirm($orderId, $defaultOrderStatusId, 'Pagamento processado com sucesso via ' . $paymentMethodCode);

            // 5. Esvazia o carrinho de compras após o sucesso
            $this->cartRepository->clear();

            // 6. Confirma todas as alterações no banco de dados de uma só vez
            $this->uow->commit();

            $response->getBody()->write(json_encode([
                'success'      => true,
                'redirect_url' => '/checkout/success',
                'message'      => 'Pagamento aprovado e pedido concluído com sucesso.'
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            // Reverte qualquer alteração no banco caso ocorra uma falha (ex: recusa de cartão)
            $this->uow->rollback();

            $response->getBody()->write(json_encode([
                'success' => false,
                'error'   => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
