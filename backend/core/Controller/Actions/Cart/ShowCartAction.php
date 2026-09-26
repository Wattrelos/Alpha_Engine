<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;
use Psr\Container\ContainerInterface;
use Alpha\Support\Presenters\ImagePresenter;
use Slim\Routing\RouteContext;

class ShowCartAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private CartRepository $cartRepository;
    private SeoUrlRepository $seoRepository;
    private ContainerInterface $container;
    private ImagePresenter $imagePresenter;
    private CustomerAddressesRepository $addressRepository;

    public function __construct(
        TwigEnvironment $twig,
        CartRepository $cartRepository,
        SeoUrlRepository $seoRepository,
        ContainerInterface $container,
        ImagePresenter $imagePresenter,
        CustomerAddressesRepository $addressRepository
    ) {
        $this->twig = $twig;
        $this->cartRepository = $cartRepository;
        $this->seoRepository = $seoRepository;
        $this->container = $container;
        $this->imagePresenter = $imagePresenter;
        $this->addressRepository = $addressRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Inicializa o contexto do carrinho (mescla sessão com login)
        $this->cartRepository->initializeContext();

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');
        $languageId = $request->getAttribute('language_id', 2);

        $config = $this->container->has('config') ? $this->container->get('config') : null;
        $currency = $this->container->has('currency') ? $this->container->get('currency') : null;
        $tax = $this->container->has('tax') ? $this->container->get('tax') : null;
        $session = $this->container->has('session') ? $this->container->get('session') : null;
        $currencyCode = $session->data['currency'] ?? ($config ? $config->get('config_currency') : 'BRL');
        $imagePresenter = $this->imagePresenter;

        // Tradução (opcional se existir)
        $translator = $this->container->has('language') ? $this->container->get('language') : null;
        $translations = [];
        if ($translator && method_exists($translator, 'load')) {
            $translator->load('cart');
            $this->twig->addGlobal('Cart', $translator->getNestedData('cart'));
            $translations = $translator->load('checkout/cart');
        }

        // Formatação de produtos do repositório
        $productsRaw = $this->cartRepository->getProducts();
        $products = [];

        foreach ($productsRaw as $prod) {
            $prodId = (int)$prod['product_id'];
            $displayId = (int)($prod['master_id'] ?? 0) > 0 ? (int)$prod['master_id'] : $prodId;
            $keyword = $this->seoRepository->getKeywordByQuery('product_id', $displayId, 0, $languageId);
            $slug = !empty($keyword) ? $keyword : $displayId;

            // Formatação do link do produto
            $href = $routeParser->urlFor('product.detail', ['lang' => $lang, 'slug' => (string)$slug]);

            // Formatação do link de remoção
            $remove = $routeParser->urlFor('cart.remove', ['lang' => $lang, 'key' => $prod['cart_id']]);

            // Formatação da imagem
            $thumb = !empty($prod['image'])
                ? $imagePresenter->resize($prod['image'], $config ? (int)$config->get('config_image_cart_width') : 90, $config ? (int)$config->get('config_image_cart_height') : 90)
                : '';

            $products[] = [
                'cart_id'        => $prod['cart_id'],
                'product_id'     => $prodId,
                'name'           => $prod['name'],
                'model'          => $prod['model'],
                'option'         => $prod['option'],
                'subscription'   => $prod['subscription'],
                'quantity'       => $prod['quantity'],
                'stock'          => $prod['stock'],
                'stock_quantity' => $prod['stock_quantity'],
                'price_raw'      => $prod['price'],
                'total_raw'      => $prod['total'],
                'price'          => $currency ? $currency->format($prod['tax_price'], $currencyCode) : 'R$ ' . number_format($prod['tax_price'], 2, ',', '.'),
                'total'          => $currency ? $currency->format($prod['tax_total'], $currencyCode) : 'R$ ' . number_format($prod['tax_total'], 2, ',', '.'),
                'href'           => $href,
                'thumb'          => $thumb,
                'remove'         => $remove
            ];
        }

        // Formatação de totais
        $totals = [];
        $taxes = $this->cartRepository->getTaxes();
        $totalVal = 0.0;
        $this->cartRepository->getTotals($totals, $taxes, $totalVal);

        // Chaves de sucesso/erro persistidas na sessão
        $errorWarning = '';
        if ($session && isset($session->data['error'])) {
            $errorWarning = $session->data['error'];
            unset($session->data['error']);
        } elseif ($session && isset($session->data['error_warning'])) {
            $errorWarning = $session->data['error_warning'];
            unset($session->data['error_warning']);
        }

        $success = '';
        if ($session && isset($session->data['success'])) {
            $success = $session->data['success'];
            unset($session->data['success']);
        }

        // URLs gerais da página
        $editUrl = $routeParser->urlFor('cart.edit', ['lang' => $lang]);
        $checkoutUrl = $routeParser->urlFor('checkout.index', ['lang' => $lang]);
        $continueUrl = $routeParser->urlFor('home', ['lang' => $lang]);

        // CEP da sessão se houver ou do cliente logado ou endereço de entrega na sessão
        $shippingCep = '';
        if ($session && !empty($session->data['shipping_cep'])) {
            $shippingCep = (string)$session->data['shipping_cep'];
        }

        if (empty($shippingCep)) {
            $customer = $this->container->has('customer') ? $this->container->get('customer') : null;
            if ($customer && $customer->isLogged()) {
                $defaultAddressId = $customer->getAddressId();
                if ($defaultAddressId > 0) {
                    $defaultAddress = $this->addressRepository->find($defaultAddressId);
                    if ($defaultAddress) {
                        $shippingCep = preg_replace('/\D/', '', $defaultAddress->getPostalCode());
                    }
                }
            }
        }

        if (empty($shippingCep)) {
            if ($session && !empty($session->data['shipping_address']['postcode'])) {
                $shippingCep = preg_replace('/\D/', '', $session->data['shipping_address']['postcode']);
            }
        }

        $title = $translations['heading_title'] ?? 'Carrinho de Compras';

        $seoData = [
            'title'       => $title . ' | meusite',
            'description' => $translations['meta_description'] ?? 'Visualize e edite os itens em seu carrinho de compras.',
            'keywords'    => 'carrinho, compras, meusite'
        ];

        $viewData = array_merge($translations, [
            'heading_title' => $title,
            'products'      => $products,
            'totals'        => $totals,
            'error_warning' => $errorWarning,
            'success'       => $success,
            'edit'          => $editUrl,
            'checkout'      => $checkoutUrl,
            'continue'      => $continueUrl,
            'shipping_cep'  => $shippingCep,
            'seo'           => $seoData,
            'title'         => $seoData['title'],
            'description'   => $seoData['description'],
            'keywords'      => $seoData['keywords'],
            'lang'          => $lang
        ]);

        $html = $this->twig->render('pages/cart/cart.twig', $viewData);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
