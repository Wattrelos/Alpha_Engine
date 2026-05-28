<?php

namespace Alpha\Controller\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Auth\Services\AuthService; // <--- Importa o nosso serviço

/*
 * O LogoutController.php segue a mesma filosofia de simplicidade do controlador de login. 
 * A única responsabilidade dele é capturar o ID da sessão que está salva no navegador do cliente, 
 * pedir para o AuthService apagá-la do Redis e, em seguida, limpar o cookie do navegador.
 */

class LogoutController
{
    private $authService;

    public function __construct()
    {
        // Instancia o serviço de autenticação
        $this->authService = new AuthService();
    }

    public function logout(Request $request, Response $response): Response
    {
        // 1. Pega os cookies enviados na requisição do cliente
        $cookies = $request->getCookieParams();
        $sessionId = $cookies['session_id'] ?? '';

        if (!empty($sessionId)) {
            // 2. Pede para o serviço destruir a sessão dentro do Redis imediatamente
            $this->authService->destroySession($sessionId);
        }

        // 3. Limpa e expira o cookie no navegador do cliente
        // Definir a data de expiração no passado (1970) força o navegador a deletar o cookie na hora
        header("Set-Cookie: session_id=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; HttpOnly; SameSite=Lax");

        // 4. Redireciona o cliente de volta para a tela pública de login
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
