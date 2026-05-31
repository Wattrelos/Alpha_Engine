<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Support\Registry;
use Alpha\Model\Domain\Repositories\CartRepository;

/**
 * SyncCartAction - Sincroniza o carrinho local (localStorage) com o banco de dados.
 * 
 * Executado logo após o login do cliente para persistir itens temporários.
 */
class SyncCartAction implements ActionInterface
{
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->registry->get('customer');

        if (!$customer->isLogged()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error'   => 'customer_not_logged'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $body = json_decode($request->getBody()->getContents(), true);
        $items = $body['items'] ?? [];

        $repositoryFactory = $this->registry->get('alpha_repository_factory');
        /** @var CartRepository $cartRepository */
        $cartRepository = $repositoryFactory->get(CartRepository::class);
        $cartRepository->initializeContext();

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];
            $options = $item['option'] ?? [];
            $cartRepository->add($productId, $quantity, $options);
        }

        $response->getBody()->write(json_encode([
            'success' => true
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
