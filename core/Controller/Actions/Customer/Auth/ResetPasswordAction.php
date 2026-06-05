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

class ResetPasswordAction implements ActionInterface
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

        $queryParams = $request->getQueryParams();
        $code = isset($queryParams['code']) ? trim((string)$queryParams['code']) : '';

        if (empty($code)) {
            $parsedBody = $request->getParsedBody();
            $code = isset($parsedBody['code']) ? trim((string)$parsedBody['code']) : '';
        }

        $customer = null;
        if (!empty($code)) {
            // Busca o cliente pelo código do token
            $customer = $this->customerRepository->findOneBy(['code' => $code]);
        }

        $customerHelper = $this->container->has('customer') ? $this->container->get('customer') : null;
        $isLogged = $customerHelper && $customerHelper->isLogged();

        if (!$customer && $isLogged) {
            $customer = $this->customerRepository->find($customerHelper->getId());
        }

        if (!$customer) {
            if (empty($code)) {
                return $response->withHeader('Location', $routeParser->urlFor('login.form', ['lang' => $lang]))->withStatus(302);
            }
            // Token inválido ou expirado
            $html = $this->twig->render('pages/errors/404.html.twig', [
                'title'       => 'Link de Redefinição Inválido | AgSonhos',
                'description' => 'O link de redefinição de senha é inválido ou já expirou.',
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(404);
        }

        $error = null;
        $success = null;

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $password = isset($parsedBody['password']) ? (string)$parsedBody['password'] : '';
            $confirm = isset($parsedBody['confirm']) ? (string)$parsedBody['confirm'] : '';

            $errors = $this->customerRepository->validatePasswordData([
                'password' => $password,
                'confirm'  => $confirm
            ]);

            if (!empty($errors)) {
                $error = reset($errors); // Pega o primeiro erro retornado
            } else {
                // Atualiza a senha e limpa o código de reset
                $this->customerRepository->updatePassword($customer->getId(), $password);
                
                // Recarrega a entidade para limpar o código
                $customer = $this->customerRepository->find($customer->getId());
                if ($customer) {
                    $customer->setCode('');
                    $this->customerRepository->updateProfile($customer);
                }

                // Insere mensagem de sucesso na sessão
                $session = $this->container->has('session') ? $this->container->get('session') : null;
                if ($session) {
                    $session->data['success'] = $isLogged
                        ? 'Sua senha foi alterada com sucesso!'
                        : 'Sua senha foi redefinida com sucesso! Agora você pode fazer login.';
                }

                $redirectUrl = $isLogged
                    ? $routeParser->urlFor('account.index', ['lang' => $lang])
                    : $routeParser->urlFor('login.form', ['lang' => $lang]);

                return $response->withHeader('Location', $redirectUrl)->withStatus(302);
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
            ['text' => 'Redefinir Senha', 'href' => $routeParser->urlFor('account.resetar-senha', ['lang' => $lang])]
        ];

        $html = $this->twig->render('pages/auth/reset-password.html.twig', [
            'direction'   => 'ltr',
            'lang'        => $language ? $language->getCode() : 'pt-br',
            'title'       => 'Redefinir Senha | AgSonhos',
            'description' => 'Redefina a senha de sua conta.',
            'breadcrumbs' => $breadcrumbs,
            'action'      => $routeParser->urlFor('account.resetar-senha', ['lang' => $lang]),
            'code'        => $code,
            'error'       => $error
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
