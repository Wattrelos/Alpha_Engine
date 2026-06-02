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

        $paymentAddress = $session->data['payment_address'] ?? [];
        $shippingAddress = $session->data['shipping_address'] ?? [];

        // Se o cliente estiver logado, tenta recuperar o endereço padrão do banco de dados
        $customer = $this->registry->get('customer');
        if ($customer && $customer->isLogged()) {
            $customerId = (int)$customer->getId();
            if (empty($paymentAddress)) {
                /** @var \Alpha\Model\Domain\Repositories\AddressRepository $addressRepository */
                $addressRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\AddressRepository::class);
                $defaultAddress = $addressRepository->getDefaultAddress($customerId);
                if ($defaultAddress) {
                    $paymentAddress = $addressRepository->getAddress($defaultAddress->getId());
                }
            }
            if (empty($shippingAddress)) {
                $shippingAddress = $paymentAddress;
            }
        }

        // Se payment_address estiver vazio mas shipping_address tiver dados do ViaCEP/simulador
        if (empty($paymentAddress) && !empty($shippingAddress)) {
            $paymentAddress = $shippingAddress;
        }
        if (empty($shippingAddress) && !empty($paymentAddress)) {
            $shippingAddress = $paymentAddress;
        }

        // Resolução de UF (código do estado como SP, RJ) a partir de IDs de estados, se necessário
        /** @var \Alpha\Model\Domain\Repositories\ZoneRepository $zoneRepository */
        $zoneRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\ZoneRepository::class);

        $getZoneCode = function($address) use ($zoneRepository) {
            if (isset($address['zone']) && !empty($address['zone']) && !is_numeric($address['zone'])) {
                return $address['zone'];
            }
            if (isset($address['zone_id']) && !empty($address['zone_id'])) {
                /** @var \Alpha\Model\Domain\Entities\Zone|null $zone */
                $zone = $zoneRepository->find((int)$address['zone_id']);
                if ($zone) {
                    return $zone->getCode();
                }
            }
            return '';
        };

        $paymentZoneCode = $getZoneCode($paymentAddress);
        $shippingZoneCode = $getZoneCode($shippingAddress);

        $viewData = array_merge($languageData, [
            'breadcrumbs' => $breadcrumbs,
            'countries'   => $countries,
            'action'      => $routeParser->urlFor('checkout.submit', ['lang' => $lang]),
            'login_email' => $session->data['email'] ?? '',
            'forgotten'   => '/' . $lang . '/forgotten', // Rota pública de esqueci a senha
            'error_warning' => $session->data['error'] ?? '',
            
            // Valores padrão dos campos de endereço / cadastro
            'payment_firstname' => $paymentAddress['firstname'] ?? ($customer ? $customer->getFirstname() : ''),
            'payment_lastname'  => $paymentAddress['lastname'] ?? ($customer ? $customer->getLastname() : ''),
            'payment_company'   => $paymentAddress['company'] ?? '',
            'payment_address_1' => $paymentAddress['address_1'] ?? '',
            'payment_number'    => $paymentAddress['number'] ?? '',
            'payment_address_2' => $paymentAddress['address_2'] ?? '',
            'payment_neighborhood' => $paymentAddress['neighborhood'] ?? '',
            'payment_city'      => $paymentAddress['city'] ?? '',
            'payment_postcode'  => $paymentAddress['postcode'] ?? '',
            'payment_country_id'=> $paymentAddress['country_id'] ?? $config->get('config_country_id'),
            'payment_zone_id'   => !empty($paymentZoneCode) ? $paymentZoneCode : ($config->get('config_zone_id') ? $getZoneCode(['zone_id' => $config->get('config_zone_id')]) : ''),
            
            'shipping_firstname' => $shippingAddress['firstname'] ?? ($customer ? $customer->getFirstname() : ''),
            'shipping_lastname'  => $shippingAddress['lastname'] ?? ($customer ? $customer->getLastname() : ''),
            'shipping_company'   => $shippingAddress['company'] ?? '',
            'shipping_address_1' => $shippingAddress['address_1'] ?? '',
            'shipping_number'    => $shippingAddress['number'] ?? '',
            'shipping_address_2' => $shippingAddress['address_2'] ?? '',
            'shipping_neighborhood' => $shippingAddress['neighborhood'] ?? '',
            'shipping_city'      => $shippingAddress['city'] ?? '',
            'shipping_postcode'  => $shippingAddress['postcode'] ?? '',
            'shipping_country_id'=> $shippingAddress['country_id'] ?? $config->get('config_country_id'),
            'shipping_zone_id'   => !empty($shippingZoneCode) ? $shippingZoneCode : ($config->get('config_zone_id') ? $getZoneCode(['zone_id' => $config->get('config_zone_id')]) : ''),
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
