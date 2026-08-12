<?php
namespace Alpha\Controller\Actions\Customer\Account;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\WishlistRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

/**
 * Action responsável por adicionar um produto à lista de desejos do cliente.
 */
class WishlistAddAction implements ActionInterface
{
    public function __construct(
        private readonly WishlistRepository $wishlistRepository,
        private readonly ContainerInterface $container
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');
        $lang = $request->getAttribute('lang', 'pt-br');

        // Se não estiver logado, retorna erro informando a necessidade de login
        if (!$customer || !$customer->isLogged()) {
            $data = [
                'success' => false,
                'error' => 'Você precisa fazer login para adicionar produtos à sua lista de desejos.',
                'redirect' => '/' . $lang . '/login'
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $parsedBody = $request->getParsedBody();
        $productId = isset($parsedBody['product_id']) ? (int)$parsedBody['product_id'] : 0;

        if ($productId <= 0) {
            $data = [
                'success' => false,
                'error' => 'ID do produto inválido.'
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $this->wishlistRepository->addWishlist($productId);
            $total = $this->wishlistRepository->getTotalWishlist();

            $data = [
                'success' => true,
                'message' => 'Produto adicionado à sua lista de desejos!',
                'total' => $total
            ];
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => 'Ocorreu um erro ao processar a requisição: ' . $e->getMessage()
            ];
        }

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
