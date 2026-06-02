<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CartRepository;
use Slim\Routing\RouteContext;

class RemoveCartAction implements ActionInterface
{
    private CartRepository $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $cartId = isset($args['key']) ? (int)$args['key'] : 0;

        $this->cartRepository->initializeContext();

        if ($cartId > 0) {
            $this->cartRepository->remove($cartId);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');
        $redirectUrl = $routeParser->urlFor('cart.index', ['lang' => $lang]);

        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }
}

