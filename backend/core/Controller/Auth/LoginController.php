<?php

namespace Alpha\Controller\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Auth\Services\AuthService; // <--- Importa o nosso novo serviço

/*
 * O LoginController.php é o porteiro da área administrativa. 
 * Sua única função é receber o formulário de login, entregar os dados ao AuthService para validação e, 
 * se estiver correto, criar a chave de sessão no Redis e liberar o acesso.
 */

class LoginController
{
    private $authService;

    public function __construct()
    {
        // Instancia o serviço de autenticação
        $this->authService = new AuthService();
    }

    public function login(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $email = $params['email'] ?? '';
        $password = $params['password'] ?? '';

        // 1. Pede para o serviço validar as credenciais
        $user = $this->authService->authenticate($email, $password);

        if (!$user) {
            // Se falhar, joga de volta para a tela de login com erro
            return $response->withHeader('Location', '/login?error=1')->withStatus(302);
        }

        // 2. Pede para o serviço gerar a sessão no Redis
        $sessionId = $this->authService->createSession($user);

        // 3. Cria o Cookie seguro no navegador do cliente
        header("Set-Cookie: session_id={$sessionId}; Path=/; HttpOnly; SameSite=Lax");

        // Redireciona o cliente para o dashboard logado
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
}
