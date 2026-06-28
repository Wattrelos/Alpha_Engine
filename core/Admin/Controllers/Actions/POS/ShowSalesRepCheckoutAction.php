<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\POS;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderRepository;

/**
 * Action que renderiza a tela de sucesso da pré-venda com o número do ticket.
 */
class ShowSalesRepCheckoutAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $orderId = isset($queryParams['order_id']) ? (int)$queryParams['order_id'] : 0;

        $loggedAdmin = $request->getAttribute('logged_admin');
        
        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->getRepository(OrderRepository::class);
        $order = $orderRepo->getOrder($orderId);

        $html = $this->getTemplate(' pos/sales-rep/checkout.twig', [
            'title' => 'Pré-Venda Concluída | PDV',
            'logged_admin' => $loggedAdmin,
            'order_id' => $orderId,
            'order' => $order,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
