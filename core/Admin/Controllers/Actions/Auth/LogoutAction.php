<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Auth\Services\AdminAuthService;
use Slim\Routing\RouteContext;

/**
 * LogoutAction - Processa o encerramento da sessão administrativa de forma limpa.
 */
class LogoutAction implements ActionInterface
{
    private AdminAuthService $authService;

    public function __construct(AdminAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $cookies = $request->getCookieParams();
        $sessionId = $cookies['admin_session_id'] ?? '';

        if (!empty($sessionId)) {
            $this->authService->destroySession($sessionId);
        }

        // Define o cabeçalho Set-Cookie para expirar/limpar o cookie do painel administrativo
        $cookieValue = 'admin_session_id=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; HttpOnly; SameSite=Lax';

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $redirectUrl = $routeParser->urlFor('admin.login.form');

        return $response
            ->withHeader('Set-Cookie', $cookieValue)
            ->withHeader('Location', $redirectUrl)
            ->withStatus(302);
    }
}
