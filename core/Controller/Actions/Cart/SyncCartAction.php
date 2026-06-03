<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Container\ContainerInterface;
use Alpha\Model\Domain\Repositories\CartRepository;
use Slim\Routing\RouteContext;

/**
 * SyncCartAction - Sincroniza o carrinho local (localStorage) com o banco de dados.
 * 
 * Executado logo após o login do cliente para persistir itens temporários.
 */
class SyncCartAction implements ActionInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');

        $body = json_decode($request->getBody()->getContents(), true);
        $items = $body['items'] ?? [];

        /** @var CartRepository $cartRepository */
        $cartRepository = \RepositoryFactory::getInstance()->get(CartRepository::class);
        $cartRepository->initializeContext();

        // Se for visitante (não logado), limpamos o carrinho da sessão atual
        // antes de sincronizar para espelhar exatamente o localStorage
        if (!$customer->isLogged()) {
            $cartRepository->clear();
        }

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
