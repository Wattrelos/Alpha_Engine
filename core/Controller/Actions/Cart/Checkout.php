<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;

class Checkout implements ActionInterface
{
    private Environment $twig;
    private ContainerInterface $container;

    private \Alpha\Support\Language $translator;

    public function __construct(Environment $twig, ContainerInterface $container, \Alpha\Support\Language $translator)
    {
        $this->twig = $twig;
        $this->container = $container;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->translator->load('checkout');
        $this->twig->addGlobal('Checkout', $this->translator->getNestedData('checkout'));

        $configSettings = $this->container->get('configSettings');
        $repositoryFactory = $this->container->get('alpha_repository_factory');

        /** @var \Alpha\Model\Domain\Repositories\CartRepository $cartRepository */
        $cartRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\CartRepository::class);
        $cartRepository->initializeContext();

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        // Se o carrinho estiver vazio, redireciona de volta para a página de carrinho
        if (!$cartRepository->hasProducts()) {
            return $response->withHeader('Location', $routeParser->urlFor('cart.index', ['lang' => $lang]))->withStatus(302);
        }

        $languageData = $this->translator->load('checkout/checkout');
        if (!isset($languageData['text_home'])) {
            $languageData['text_home'] = 'Principal';
        }
        if (!isset($languageData['heading_title'])) {
            $languageData['heading_title'] = 'Finalizar Compra';
        }

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

        // Mapear dados da sessão (se existirem)
        $paymentAddress = $_SESSION['payment_address'] ?? [];
        $shippingAddress = $_SESSION['shipping_address'] ?? [];

        // Se o cliente estiver logado, tenta recuperar o endereço padrão do banco de dados
        $customerId = (int)($_SESSION['customer_id'] ?? 0);
        /** @var \Alpha\Model\Domain\Repositories\CustomerRepository $customerRepository */
        $customerRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\CustomerRepository::class);
        $customer = $customerId > 0 ? $customerRepository->find($customerId) : null;

        if ($customer) {
            if (empty($paymentAddress)) {
                /** @var \Alpha\Model\Domain\Repositories\CustomerAddressesRepository $addressRepository */
                $addressRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\CustomerAddressesRepository::class);
                $defaultAddressId = $customer->getAddressId();
                if ($defaultAddressId > 0) {
                    $addresses = $addressRepository->getAddresses($customerId);
                    foreach ($addresses as $addr) {
                        if ((int)$addr['address_id'] === $defaultAddressId) {
                            $paymentAddress = $addr;
                            break;
                        }
                    }
                    if (empty($paymentAddress) && !empty($addresses)) {
                        $paymentAddress = $addresses[0];
                    }
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
        /** @var \Alpha\Model\Domain\Repositories\GeoZoneRepository $zoneRepository */
        $zoneRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\GeoZoneRepository::class);

        $getZoneCode = function ($address) use ($zoneRepository) {
            if (isset($address['zone']) && !empty($address['zone']) && !is_numeric($address['zone'])) {
                return $address['zone'];
            }
            if (isset($address['zone_id']) && !empty($address['zone_id'])) {
                /** @var \Alpha\Model\Domain\Entities\Geo\Zone|null $zone */
                $zone = $zoneRepository->find((int)$address['zone_id']);
                if ($zone) {
                    $isoCode = $zone->getIsoCode();
                    return str_contains($isoCode, '-') ? explode('-', $isoCode)[1] : $isoCode;
                }
            }
            return '';
        };

        $paymentZoneCode = $getZoneCode($paymentAddress);
        $shippingZoneCode = $getZoneCode($shippingAddress);

        /** @var \Alpha\Mappers\MapperFactory $mapperFactory */
        $mapperFactory = $this->container->get('alpha_mapper_factory');
        /** @var \Alpha\Mappers\EntityMappers\GeoCountryMapper $countryMapper */
        $countryMapper = $mapperFactory->get(\Alpha\Mappers\EntityMappers\GeoCountryMapper::class);
        $countriesList = array_map(fn($c) => [
            'id' => $c->getId(),
            'name' => $c->getName()
        ], $countryMapper->getCountries());

        $viewData = array_merge($languageData, [
            'breadcrumbs' => $breadcrumbs,
            'countries'   => $countriesList,
            'action'      => $routeParser->urlFor('checkout.submit', ['lang' => $lang]),
            'login_email' => $_SESSION['email'] ?? '',
            'forgotten'   => '/' . $lang . '/forgotten', // Rota pública de esqueci a senha
            'error_warning' => $_SESSION['error'] ?? '',

            // Valores padrão dos campos de endereço / cadastro
            'payment_firstname' => $paymentAddress['firstname'] ?? ($customer ? $customer->getFirstname() : ''),
            'payment_lastname'  => $paymentAddress['lastname'] ?? ($customer ? $customer->getLastname() : ''),
            'payment_company'   => $paymentAddress['company'] ?? '',
            'payment_street' => $paymentAddress['street'] ?? '',
            'payment_number'    => $paymentAddress['number'] ?? '',
            'payment_complement' => $paymentAddress['complement'] ?? '',
            'payment_neighborhood' => $paymentAddress['neighborhood'] ?? '',
            'payment_city'      => $paymentAddress['city'] ?? '',
            'payment_postcode'  => $paymentAddress['postcode'] ?? '',
            'payment_country_id' => $paymentAddress['country_id'] ?? ($configSettings['config_country_id'] ?? 76),
            'payment_zone_id'   => !empty($paymentZoneCode) ? $paymentZoneCode : (isset($configSettings['config_zone_id']) ? $getZoneCode(['zone_id' => $configSettings['config_zone_id']]) : ''),

            'shipping_firstname' => $shippingAddress['firstname'] ?? ($customer ? $customer->getFirstname() : ''),
            'shipping_lastname'  => $shippingAddress['lastname'] ?? ($customer ? $customer->getLastname() : ''),
            'shipping_company'   => $shippingAddress['company'] ?? '',
            'shipping_street' => $shippingAddress['street'] ?? '',
            'shipping_number'    => $shippingAddress['number'] ?? '',
            'shipping_complement' => $shippingAddress['complement'] ?? '',
            'shipping_neighborhood' => $shippingAddress['neighborhood'] ?? '',
            'shipping_city'      => $shippingAddress['city'] ?? '',
            'shipping_postcode'  => $shippingAddress['postcode'] ?? '',
            'shipping_country_id' => $shippingAddress['country_id'] ?? ($configSettings['config_country_id'] ?? 76),
            'shipping_zone_id'   => !empty($shippingZoneCode) ? $shippingZoneCode : (isset($configSettings['config_zone_id']) ? $getZoneCode(['zone_id' => $configSettings['config_zone_id']]) : ''),
        ]);

        unset($_SESSION['error']);

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
