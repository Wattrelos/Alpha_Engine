<?php

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Auth\Services\CustomerAuthService;
use Slim\Routing\RouteContext;

/**
 * LogoutAction - Processa o encerramento da sessão de forma limpa.
 */
class LogoutAction implements ActionInterface
{
    private CustomerAuthService $authService;

    public function __construct(CustomerAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // 1. Pega os cookies do cliente
        $cookies = $request->getCookieParams();
        $sessionId = $cookies['session_id'] ?? '';

        if (!empty($sessionId)) {
            // 2. Destrói a sessão de forma segura (no Redis ou $_SESSION)
            $this->authService->destroySession($sessionId);
        }

        // 3. Define o cabeçalho Set-Cookie para expirar/limpar o cookie do navegador de forma segura
        $cookieValue = \Alpha\Support\CookieHelper::makeCookieHeader($request, 'session_id', '', -1);

        // 4. Redireciona para a tela de login pública de forma dinâmica
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');
        $redirectUrl = $routeParser->urlFor('login.form', ['lang' => $lang]);

        return $response
            ->withHeader('Set-Cookie', $cookieValue)
            ->withHeader('Location', $redirectUrl)
            ->withStatus(302);
    }
}

