<?php

namespace Alpha\Controller\Actions\Customer\Auth;

use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Twig\Environment as TwigEnvironment;
use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;

class RequestPasswordResetAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private CustomerRepository $customerRepository;
    private LanguageRepository $languageRepository;
    private SettingRepository $settingRepository;
    private ContainerInterface $container;

    public function __construct(
        TwigEnvironment $twig,
        CustomerRepository $customerRepository,
        LanguageRepository $languageRepository,
        SettingRepository $settingRepository,
        ContainerInterface $container
    ) {
        $this->twig = $twig;
        $this->customerRepository = $customerRepository;
        $this->languageRepository = $languageRepository;
        $this->settingRepository = $settingRepository;
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $lang = $request->getAttribute('lang', 'pt-br');
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $error = null;
        $success = null;
        $email = '';

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $email = isset($parsedBody['email']) ? trim((string)$parsedBody['email']) : '';

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Por favor, insira um endereço de e-mail válido.';
            } else {
                $customer = $this->customerRepository->findByEmail($email);
                if ($customer) {
                    // Gera o token de reset e salva
                    $code = sha1(uniqid((string)mt_rand(), true));
                    $customer->setCode($code);
                    $this->customerRepository->updateProfile($customer);

                    $resetUrl = HTTP_SERVER . $lang . '/resetar-senha?code=' . $code;
                    
                    // Simula envio de e-mail salvando em logs
                    $logMessage = "Password reset request for " . $email . ". Link: " . $resetUrl . "\n";
                    file_put_contents(DIR_STORAGE . 'logs/password_reset.log', $logMessage, FILE_APPEND);

                    $success = 'Se o e-mail informado estiver cadastrado, você receberá um link para redefinir sua senha em instantes.';
                    
                    // Facilidade para ambiente de desenvolvimento:
                    if (str_contains(HTTP_SERVER, 'localhost') || str_contains(HTTP_SERVER, '127.0.0.1')) {
                        $success .= ' (Dev Mode - Link gerado: ' . $resetUrl . ')';
                    }
                } else {
                    // Por questões de segurança, exibimos a mesma mensagem para evitar enumeração de usuários
                    $success = 'Se o e-mail informado estiver cadastrado, você receberá um link para redefinir sua senha em instantes.';
                }
            }
        }

        $configSettings = $this->settingRepository->getSetting('config', 1);
        $languageCode = $configSettings['config_language_catalog'] ?? 'pt-br';
        $language = $this->languageRepository->getByCode($languageCode);
        if (!$language) {
            $language = $this->languageRepository->find(2);
        }

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Login', 'href' => $routeParser->urlFor('login.form', ['lang' => $lang])],
            ['text' => 'Recuperar Senha', 'href' => $routeParser->urlFor('account.recuperar-senha', ['lang' => $lang])]
        ];

        $html = $this->twig->render('pages/auth/request-password-reset.html.twig', [
            'direction'   => 'ltr',
            'lang'        => $language ? $language->getCode() : 'pt-br',
            'title'       => 'Recuperar Senha | AgSonhos',
            'description' => 'Recupere o acesso à sua conta.',
            'breadcrumbs' => $breadcrumbs,
            'action'      => $routeParser->urlFor('account.recuperar-senha', ['lang' => $lang]),
            'back'        => $routeParser->urlFor('login.form', ['lang' => $lang]),
            'email'       => $email,
            'error'       => $error,
            'success'     => $success
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
