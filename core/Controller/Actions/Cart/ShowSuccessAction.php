<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;

class ShowSuccessAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private ContainerInterface $container;

    public function __construct(TwigEnvironment $twig, ContainerInterface $container)
    {
        $this->twig = $twig;
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $session = $this->container->get('session');

        $orderId = $session->data['last_order_id'] ?? $session->data['order_id'] ?? 0;

        // Limpeza profunda de resíduos de checkout na sessão
        $keysToClear = [
            'last_order_id', 'order_id', 'payment_method', 'shipping_method', 
            'payment_address', 'shipping_address', 'comment', 
            'coupon', 'reward', 'voucher', 'vouchers', 'totals', 'cart'
        ];

        foreach ($keysToClear as $key) {
            if (isset($session->data[$key])) {
                unset($session->data[$key]);
            }
        }

        $lang = $args['lang'] ?? 'pt-br';
        $html = $this->twig->render('pages/cart/success.twig', [
            'title'        => 'Compra Concluída com Sucesso!',
            'order_id'     => $orderId,
            'continue_url' => '/' . $lang,
            'lang'         => $lang
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
