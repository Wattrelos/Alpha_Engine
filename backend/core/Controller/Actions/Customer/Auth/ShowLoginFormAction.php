<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Slim\Routing\RouteContext;

/**
 * ShowLoginFormAction - Exibe o formulário de login para o cliente.
 */
class ShowLoginFormAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private SettingRepository $settingRepository;
    private LanguageRepository $languageRepository;

    public function __construct(TwigEnvironment $twig, SettingRepository $settingRepository, LanguageRepository $languageRepository)
    {
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
            ['text' => 'Login', 'href' => $routeParser->urlFor('login.form', ['lang' => $lang])]
        ];

        // Se houver algum erro ou sucesso na URL (ex: ?error=1 ou ?success=1)
        $queryParams = $request->getQueryParams();
        $errorWarning = isset($queryParams['error']) ? 'Aviso: Seu endereço de e-mail e/ou senha não coincidem.' : null;
        $success = isset($queryParams['success']) ? 'Sucesso: Sua conta foi atualizada.' : null;

        $html = $this->twig->render('pages/users/login.twig', [
            'direction' => 'ltr',
            'lang' => $language ? $language->getCode() : 'pt-br',
            'title' => 'Acessar Conta | AgSonhos',
            'description' => 'Acesse sua conta para gerenciar seus pedidos e compras.',
            'breadcrumbs' => $breadcrumbs,
            
            // Variáveis de idioma para o bloco de Novo Cliente
            'text_new_customer' => 'Novo Cliente',
            'text_register' => 'Cadastrar Conta',
            'text_register_account' => 'Ao criar uma conta, você poderá comprar mais rápido, acompanhar o status do seu pedido e controlar seus pedidos anteriores.',
            'button_continue' => 'Continuar',
            'register' => $routeParser->urlFor('register.form', ['lang' => $lang]), // Rota pública de registro

            // Variáveis de idioma para o formulário de login
            'text_returning_customer' => 'Cliente Registrado',
            'text_i_am_returning_customer' => 'Já sou cliente',
            'entry_email' => 'Endereço de E-mail',
            'entry_password' => 'Senha',
            'text_forgotten' => 'Esqueceu a senha?',
            'forgotten' => $routeParser->urlFor('account.recuperar-senha', ['lang' => $lang]), // Rota pública de esqueci a senha
            'button_login' => 'Acessar',
            'login' => $routeParser->urlFor('login.submit', ['lang' => $lang]), // Action do formulário

            // Alertas
            'error_warning' => $errorWarning,
            'success' => $success,

            // Outras configurações
            'email' => '',
            'password' => '',
            'redirect' => $queryParams['redirect'] ?? ''
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
