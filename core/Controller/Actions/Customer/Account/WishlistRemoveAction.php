<?php
namespace Alpha\Controller\Actions\Customer\Account;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\WishlistRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

/**
 * Action responsável por remover um produto da lista de desejos.
 */
class WishlistRemoveAction implements ActionInterface
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

        if (!$customer || !$customer->isLogged()) {
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $productId = isset($args['product_id']) ? (int)$args['product_id'] : 0;
        $session = $this->container->has('session') ? $this->container->get('session') : null;

        if ($productId > 0) {
            try {
                $this->wishlistRepository->deleteWishlist($productId);
                if ($session) {
                    $session->data['success'] = 'Produto removido da lista de desejos com sucesso.';
                }
            } catch (\Exception $e) {
                if ($session) {
                    $session->data['error_warning'] = 'Ocorreu um erro ao remover o produto da lista de desejos.';
                }
            }
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $redirectUrl = $routeParser->urlFor('account.wishlist', ['lang' => $lang]);

        return $response
            ->withHeader('Location', $redirectUrl)
            ->withStatus(302);
    }
}
