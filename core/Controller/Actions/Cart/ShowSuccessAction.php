<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Support\Registry;
use Slim\Routing\RouteContext;

class ShowSuccessAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private Registry $registry;

    public function __construct(TwigEnvironment $twig, Registry $registry)
    {
        $this->twig = $twig;
        $this->registry = $registry;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $session = $this->registry->get('session');

        $orderId = $session->data['last_order_id'] ?? 0;
        unset($session->data['last_order_id']);

        $lang = $args['lang'] ?? 'pt-br';
        $html = $this->twig->render('pages/cart/success.twig', [
            'order_id' => $orderId,
            'lang'     => $lang
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
