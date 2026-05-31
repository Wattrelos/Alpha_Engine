<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

class OrdersAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private OrderRepository $orderRepository;
    private SettingRepository $settingRepository;

    public function __construct(TwigEnvironment $twig, OrderRepository $orderRepository, SettingRepository $settingRepository)
    {
        $this->twig = $twig;
        $this->orderRepository = $orderRepository;
        $this->settingRepository = $settingRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Meus Pedidos', 'href' => $routeParser->urlFor('account.orders', ['lang' => $lang])]
        ];

        // Busca pedidos do cliente logado
        $ordersData = $this->orderRepository->getOrders(0, 20);
        $orders = [];

        foreach ($ordersData as $order) {
            $productTotal = $this->orderRepository->getTotalProductsByOrderId((int)$order['order_id']);
            $totalFormatted = 'R$ ' . number_format((float)$order['total'], 2, ',', '.');

            $orders[] = [
                'order_id'      => $order['order_id'],
                'product_total' => $productTotal,
                'status'        => $order['status'],
                'total'         => $totalFormatted,
                'date_added'    => date('d/m/Y H:i:s', strtotime($order['date_added'])),
                'view'          => $routeParser->urlFor('account.order.history', ['lang' => $lang, 'order_id' => (string)$order['order_id']])
            ];
        }

        $data = [
            'breadcrumbs'          => $breadcrumbs,
            'heading_title'        => 'Meus Pedidos',
            'column_order_id'      => 'ID do Pedido',
            'column_product_total' => 'Qtd. Itens',
            'column_status'        => 'Situação',
            'column_total'         => 'Total',
            'column_date_added'    => 'Data de Cadastro',
            'button_view'          => 'Visualizar',
            'button_continue'      => 'Continuar',
            'text_no_results'      => 'Você ainda não possui pedidos cadastrados.',
            'orders'               => $orders,
            'continue'             => $routeParser->urlFor('account.index', ['lang' => $lang]),
            'column_left'          => '',
            'column_right'         => '',
            'content_top'          => '',
            'content_bottom'       => '',
            'pagination'           => '',
            'results'              => ''
        ];

        $html = $this->twig->render('pages/users/accounts/orders.twig', $data);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
