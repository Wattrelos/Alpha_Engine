<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Support\Registry;
use Alpha\Model\Domain\Repositories\CountryRepository;
use Slim\Routing\RouteContext;

class Checkout implements ActionInterface
{
    private Environment $twig;
    private Registry $registry;

    public function __construct(Environment $twig, Registry $registry)
    {
        $this->twig = $twig;
        $this->registry = $registry;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $language = $this->registry->get('language');
        $languageData = $language ? $language->load('checkout/checkout') : [];
        $config = $this->registry->get('config');

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        // Breadcrumbs
        $breadcrumbs = [];
        $breadcrumbs[] = [
            'text' => $languageData['text_home'] ?? 'Principal',
            'href' => $routeParser->urlFor('home', ['lang' => $lang])
        ];
        $breadcrumbs[] = [
            'text' => $languageData['heading_title'] ?? 'Finalizar Compra',
            'href' => $routeParser->urlFor('checkout.index', ['lang' => $lang])
        ];

        // Buscar Países para o select de endereço
        $repositoryFactory = $this->registry->get('alpha_repository_factory');
        /** @var CountryRepository $countryRepository */
        $countryRepository = $repositoryFactory->get(CountryRepository::class);
        $countries = $countryRepository->getCountries();

        // Mapear dados da sessão (se existirem)
        $session = $this->registry->get('session');

        $viewData = array_merge($languageData, [
            'breadcrumbs' => $breadcrumbs,
            'countries'   => $countries,
            'action'      => $routeParser->urlFor('checkout.submit', ['lang' => $lang]),
            'login_email' => $session->data['email'] ?? '',
            'forgotten'   => '/' . $lang . '/forgotten', // Rota pública de esqueci a senha
            'error_warning' => $session->data['error'] ?? '',
            
            // Valores padrão dos campos de endereço / cadastro
            'payment_firstname' => $session->data['payment_address']['firstname'] ?? '',
            'payment_lastname'  => $session->data['payment_address']['lastname'] ?? '',
            'payment_company'   => $session->data['payment_address']['company'] ?? '',
            'payment_address_1' => $session->data['payment_address']['address_1'] ?? '',
            'payment_number'    => $session->data['payment_address']['number'] ?? '',
            'payment_address_2' => $session->data['payment_address']['address_2'] ?? '',
            'payment_neighborhood' => $session->data['payment_address']['neighborhood'] ?? '',
            'payment_city'      => $session->data['payment_address']['city'] ?? '',
            'payment_postcode'  => $session->data['payment_address']['postcode'] ?? '',
            'payment_country_id'=> $session->data['payment_address']['country_id'] ?? $config->get('config_country_id'),
            'payment_zone_id'   => $session->data['payment_address']['zone_id'] ?? $config->get('config_zone_id'),
            
            'shipping_firstname' => $session->data['shipping_address']['firstname'] ?? '',
            'shipping_lastname'  => $session->data['shipping_address']['lastname'] ?? '',
            'shipping_company'   => $session->data['shipping_address']['company'] ?? '',
            'shipping_address_1' => $session->data['shipping_address']['address_1'] ?? '',
            'shipping_number'    => $session->data['shipping_address']['number'] ?? '',
            'shipping_address_2' => $session->data['shipping_address']['address_2'] ?? '',
            'shipping_neighborhood' => $session->data['shipping_address']['neighborhood'] ?? '',
            'shipping_city'      => $session->data['shipping_address']['city'] ?? '',
            'shipping_postcode'  => $session->data['shipping_address']['postcode'] ?? '',
            'shipping_country_id'=> $session->data['shipping_address']['country_id'] ?? $config->get('config_country_id'),
            'shipping_zone_id'   => $session->data['shipping_address']['zone_id'] ?? $config->get('config_zone_id'),
        ]);

        unset($session->data['error']);

        $title = $languageData['heading_title'] ?? 'Finalizar Compra';
        $seoData = [
            'title'       => $title . ' | AgSonhos',
            'description' => 'Finalize a sua compra com segurança na AgSonhos.',
            'keywords'    => 'checkout, finalizar compra, agsonhos'
        ];

        $html = $this->twig->render('pages/cart/checkout.twig', array_merge($viewData, [
            'seo'         => $seoData,
            'title'       => $seoData['title'],
            'description' => $seoData['description'],
            'keywords'    => $seoData['keywords']
        ]));

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
