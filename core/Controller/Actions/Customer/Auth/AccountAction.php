<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;
use Psr\Container\ContainerInterface;

class AccountAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private SettingRepository $settingRepository;
    private LanguageRepository $languageRepository;

    public function __construct(
        TwigEnvironment $twig,
        SettingRepository $settingRepository,
        LanguageRepository $languageRepository,
        private readonly ContainerInterface $container
    ) {
        $this->twig = $twig;
        $this->settingRepository = $settingRepository;
        $this->languageRepository = $languageRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $configSettings = $this->settingRepository->getSetting('config', 0);
        $languageCode = $configSettings['config_language_catalog'] ?? 'pt-br';
        $language = $this->languageRepository->getByCode($languageCode);

        if (!$language) {
            $language = $this->languageRepository->find(2); // Fallback para pt-br (ID 2)
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])]
        ];

        $translator = $this->container->get('language');
        $translator->load('account/account');
        $translationData = $translator->getNestedData('account/account');

        // Tradução e variáveis do template da conta
        $data = array_merge([
            'direction' => 'ltr',
            'lang' => $language ? $language->getCode() : 'pt-br',
            'title' => ($translationData['headingTitle'] ?? 'Minha Conta') . ' | AgSonhos',
            'description' => 'Gerencie sua conta e compras.',
            'breadcrumbs' => $breadcrumbs,

            // Links das rotas
            'edit'          => '/' . $lang . '/account/edit',
            'password'      => '/' . $lang . '/account/password',
            'payment_method' => '/' . $lang . '/account/payment',
            'address'       => $routeParser->urlFor('account.addresses', ['lang' => $lang]),
            'wishlist'      => '/' . $lang . '/account/wishlist',
            'order'         => $routeParser->urlFor('account.orders',    ['lang' => $lang]),
            'subscription'  => '/' . $lang . '/account/subscription',
            'download'      => '/' . $lang . '/account/download',
            'reward'        => '/' . $lang . '/account/reward',
            'return'        => '/' . $lang . '/account/return',
            'transaction'   => '/' . $lang . '/account/transaction',
            'affiliate'     => '/' . $lang . '/account/affiliate',
            'tracking'      => '/' . $lang . '/account/tracking',
            'newsletter'    => '/' . $lang . '/account/newsletter',

            // Estrutura
            'column_left' => '',
            'column_right' => '',
            'content_top' => '',
            'content_bottom' => '',
            'reward' => false, // Ocultar se não implementado
            'affiliate' => false // Ocultar se não implementado
        ], $translationData);

        $session = $this->container->has('session') ? $this->container->get('session') : null;
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
        $data['success'] = $success;
        $data['error_warning'] = $errorWarning;

        // Renderiza o template Twig moderno correspondente
        $html = $this->twig->render('pages/users/accounts/account.twig', $data);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
