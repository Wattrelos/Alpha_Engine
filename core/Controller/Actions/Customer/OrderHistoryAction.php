<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer;

use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

class OrderHistoryAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private OrderRepository $orderRepository;

    public function __construct(TwigEnvironment $twig, OrderRepository $orderRepository)
    {
        $this->twig = $twig;
        $this->orderRepository = $orderRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $order_id = (int)($args['order_id'] ?? 0);
        
        // O método getOrder já filtra pelo ID do cliente logado internamente
        $order = $this->orderRepository->getOrder($order_id);

        if (!$order) {
            $html = $this->twig->render('pages/errors/404.html.twig', [
                'title'       => 'Pedido Não Encontrado | AgSonhos',
                'description' => 'O pedido solicitado não existe ou não pertence a esta conta.',
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Meus Pedidos', 'href' => $routeParser->urlFor('account.orders', ['lang' => $lang])],
            ['text' => 'Histórico do Pedido #' . $order_id, 'href' => $routeParser->urlFor('account.order.history', ['lang' => $lang, 'order_id' => (string)$order_id])]
        ];

        $historiesData = $this->orderRepository->getHistories($order_id);
        $histories = [];
        foreach ($historiesData as $history) {
            $histories[] = [
                'date_added' => date('d/m/Y H:i:s', strtotime($history['date_added'])),
                'comment'    => nl2br(htmlspecialchars($history['comment'])),
                'status'     => $history['status']
            ];
        }

        $data = [
            'breadcrumbs'       => $breadcrumbs,
            'column_date_added' => 'Data de Envio',
            'column_comment'    => 'Comentários',
            'column_status'     => 'Situação',
            'text_no_results'   => 'Nenhum histórico encontrado para este pedido.',
            'histories'         => $histories,
            'continue'          => $routeParser->urlFor('account.orders', ['lang' => $lang]),
            'pagination'        => '',
            'results'           => ''
        ];

        $html = $this->twig->render('pages/users/accounts/order-history.twig', $data);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

