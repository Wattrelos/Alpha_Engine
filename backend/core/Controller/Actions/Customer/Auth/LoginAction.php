<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Auth\Services\CustomerAuthService;
use Slim\Routing\RouteContext;

/**
 * LoginAction - Processa a autenticação do cliente via requisição POST /login (AJAX).
 */
class LoginAction implements ActionInterface
{
    private CustomerAuthService $authService;

    public function __construct(CustomerAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody();
        $email = trim((string)($params['email'] ?? ''));
        $password = trim((string)($params['password'] ?? ''));
        $redirect = trim((string)($params['redirect'] ?? ''));

        // Valida as credenciais de forma segura através do AuthService (que delega para o CustomerRepository)
        $user = $this->authService->authenticate($email, $password);

        if (!$user) {
            $response->getBody()->write(json_encode([
                'error' => [
                    'warning' => 'Aviso: Seu endereço de e-mail e/ou senha não coincidem.'
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Se autenticou com sucesso, cria a sessão no Redis
        $sessionId = $this->authService->createSession($user);

        // Define o Cookie de Sessão de forma segura com suporte a HTTPS (Secure)
        $cookieValue = \Alpha\Support\CookieHelper::makeCookieHeader($request, 'session_id', $sessionId, 7200);

        // Obtém o parser de rotas para obter a URL do painel da conta
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');
        $defaultRedirect = $routeParser->urlFor('account.index', ['lang' => $lang]);

        $redirectUrl = (!empty($redirect) && str_starts_with($redirect, '/')) ? $redirect : $defaultRedirect;

        $response->getBody()->write(json_encode([
            'redirect' => $redirectUrl
        ]));

        return $response
            ->withHeader('Set-Cookie', $cookieValue)
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
