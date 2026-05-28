<?php

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Services\Auth\AuthService;

class LogoutAction
{
    private $authService;

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
            // 2. Remove do Redis
            $this->authService->destroySession($sessionId);
        }

        // 3. Limpa o cookie do navegador
        header("Set-Cookie: session_id=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; HttpOnly; SameSite=Lax");

        // 4. Redireciona para a tela de login pública
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
