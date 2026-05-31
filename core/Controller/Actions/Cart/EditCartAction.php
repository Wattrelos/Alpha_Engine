<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CartRepository;
use Slim\Routing\RouteContext;

class EditCartAction implements ActionInterface
{
    private CartRepository $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        $this->cartRepository->initializeContext();

        if (isset($data['quantity']) && is_array($data['quantity'])) {
            foreach ($data['quantity'] as $cartId => $quantity) {
                $this->cartRepository->update((int)$cartId, (int)$quantity);
            }
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');
        $redirectUrl = $routeParser->urlFor('cart.index', ['lang' => $lang]);

        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }
}

