<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CartRepository;

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

        $lang = $args['lang'] ?? 'pt-br';
        return $response->withHeader('Location', '/' . $lang . '/carrinho')->withStatus(302);
    }
}
