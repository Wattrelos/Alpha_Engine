<?php

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Auth\Services\AuthService;

/**
 * LogoutAction - Processa o encerramento da sessão de forma limpa.
 */
class LogoutAction implements ActionInterface
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
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

        // 3. Define o cabeçalho Set-Cookie para expirar/limpar o cookie do navegador
        $cookieValue = 'session_id=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; HttpOnly; SameSite=Lax';

        // 4. Redireciona para a tela de login pública
        return $response
            ->withHeader('Set-Cookie', $cookieValue)
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }
}
