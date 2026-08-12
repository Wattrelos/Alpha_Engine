<?php

namespace Alpha\Controller\Actions\Customer\Account;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\WishlistRepository;
use Psr\Container\ContainerInterface;
use Alpha\Support\Presenters\ImagePresenter;
use Twig\Environment as TwigEnvironment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

/**
 * Action responsável por orquestrar e exibir a página de Lista de Desejos do cliente logado.
 */
class WishlistAction implements ActionInterface
{
    public function __construct(
        private readonly TwigEnvironment $twig,
        private readonly WishlistRepository $wishlistRepository,
        private readonly ContainerInterface $container,
        private readonly ImagePresenter $imagePresenter
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');
        $routeContext = RouteContext::fromRequest($request);
        $routeParser  = $routeContext->getRouteParser();
        $lang         = $request->getAttribute('lang', 'pt-br');

        // Proteção de Rota (Garantia de que o visitante não visualize uma lista vazia)
        if (!$customer || !$customer->isLogged()) {
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        // Serviços Legados Injetados de forma isolada via Container PSR-11
        $config   = $this->container->has('config') ? $this->container->get('config') : null;
        $currency = $this->container->has('currency') ? $this->container->get('currency') : null;
        $session  = $this->container->has('session') ? $this->container->get('session') : null;
        $tax      = $this->container->has('tax') ? $this->container->get('tax') : null;

        $currencyCode = ($session && isset($session->data['currency'])) ? $session->data['currency'] : ($config ? $config->get('config_currency') : 'BRL');
        $wishlistItems = $this->wishlistRepository->getWishlist();

        $imagePresenter = $this->imagePresenter;
        $formattedItems = [];

        // Formatação Visual Restrita à Camada de Controller/Action
        foreach ($wishlistItems as $item) {
            $priceBase   = ($tax && $config) ? $tax->calculate($item['price'], $item['tax_class_id'], $config->get('config_tax')) : $item['price'];
            $specialBase = ($item['special'] && $tax && $config) ? $tax->calculate($item['special'], $item['tax_class_id'], $config->get('config_tax')) : $item['special'];

            $formattedItems[] = [
                'product_id' => $item['product_id'],
                'name'       => $item['name'],
                'model'      => $item['model'],
                'thumb'      => $imagePresenter->resize($item['image'] ?? '', $config ? (int)$config->get('config_image_wishlist_width') : 80, $config ? (int)$config->get('config_image_wishlist_height') : 80, false),
                'price'      => $currency ? $currency->format($priceBase, $currencyCode) : 'R$ ' . number_format($priceBase, 2, ',', '.'),
                'special'    => ($item['special'] && $currency) ? $currency->format($specialBase, $currencyCode) : false,
                'quantity'   => $item['quantity'],
                'minimum'    => $item['minimum'],
                // Geração de URLs amigáveis controladas nativamente pelo motor de rotas Slim
                'href'       => $routeParser->urlFor('product.detail', ['lang' => $lang, 'slug' => (string)$item['product_id']]),
                'remove'     => $routeParser->urlFor('account.wishlist.remove', ['lang' => $lang, 'product_id' => (string)$item['product_id']])
            ];
        }

        $success = '';
        $errorWarning = '';

        if ($session) {
            if (isset($session->data['success'])) {
                $success = $session->data['success'];
                unset($session->data['success']);
            }
            if (isset($session->data['error_warning'])) {
                $errorWarning = $session->data['error_warning'];
                unset($session->data['error_warning']);
            }
        }

        $html = $this->twig->render('pages/users/accounts/wishlist.twig', [
            'title'          => 'Minha Lista de Desejos',
            'wishlist_items' => $formattedItems,
            'continue_url'   => $routeParser->urlFor('account.index', ['lang' => $lang]),
            'lang'           => $lang,
            'success'        => $success,
            'error_warning'  => $errorWarning
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}