<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CartRepository;
use Slim\Routing\RouteContext;

class AddCartAction implements ActionInterface
{
    private CartRepository $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        $productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

        $option = [];
        if (isset($data['option'])) {
            $option = array_filter($data['option']);
        }

        $this->cartRepository->initializeContext();
        $this->cartRepository->add($productId, $quantity, $option);

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');
        
        $response->getBody()->write(json_encode([
            'success'  => true,
            'redirect' => $routeParser->urlFor('cart.index', ['lang' => $lang])
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}

