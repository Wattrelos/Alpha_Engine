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
        private readonly ContainerInterface $container
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
        $currency = $this->container->get('currency');
        $config   = $this->container->get('config');
        $session  = $this->container->get('session');
        $tax      = $this->container->get('tax');

        $currencyCode = $session->data['currency'] ?? $config->get('config_currency');
        $wishlistItems = $this->wishlistRepository->getWishlist();

        $imagePresenter = new ImagePresenter($config ? (string)$config->get('config_url') : '');
        $formattedItems = [];

        // Formatação Visual Restrita à Camada de Controller/Action
        foreach ($wishlistItems as $item) {
            $priceBase   = $tax ? $tax->calculate($item['price'], $item['tax_class_id'], $config->get('config_tax')) : $item['price'];
            $specialBase = $item['special'] && $tax ? $tax->calculate($item['special'], $item['tax_class_id'], $config->get('config_tax')) : $item['special'];

            $formattedItems[] = [
                'product_id' => $item['product_id'],
                'name'       => $item['name'],
                'model'      => $item['model'],
                'thumb'      => $imagePresenter->resize($item['image'] ?? '', (int)$config->get('config_image_wishlist_width'), (int)$config->get('config_image_wishlist_height'), false),
                'price'      => $currency->format($priceBase, $currencyCode),
                'special'    => $item['special'] ? $currency->format($specialBase, $currencyCode) : false,
                'quantity'   => $item['quantity'],
                'minimum'    => $item['minimum'],
                // Geração de URLs amigáveis controladas nativamente pelo motor de rotas Slim
                'href'       => $routeParser->urlFor('product.detail', ['lang' => $lang, 'slug' => (string)$item['product_id']]),
                'remove'     => $routeParser->urlFor('account.wishlist.remove', ['lang' => $lang, 'product_id' => (string)$item['product_id']])
            ];
        }

        $html = $this->twig->render('pages/account/wishlist.twig', [
            'title'          => 'Minha Lista de Desejos',
            'wishlist_items' => $formattedItems,
            'continue_url'   => $routeParser->urlFor('account.index', ['lang' => $lang]),
            'lang'           => $lang
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}