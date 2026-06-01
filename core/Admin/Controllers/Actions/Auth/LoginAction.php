<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Auth\Services\AdminAuthService;
use Alpha\Model\Domain\Repositories\UserRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

/**
 * LoginAction - Processa a tentativa de login no painel administrativo.
 */
class LoginAction implements ActionInterface
{
    private AdminAuthService $authService;
    private UserRepository $userRepository;
    private TwigEnvironment $twig;

    public function __construct(AdminAuthService $authService, UserRepository $userRepository, TwigEnvironment $twig)
    {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $parsedBody = $request->getParsedBody();
        $username = trim((string)($parsedBody['username'] ?? ''));
        $password = trim((string)($parsedBody['password'] ?? ''));

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $serverParams = $request->getServerParams();
        $ip = $serverParams['REMOTE_ADDR'] ?? '127.0.0.1';

        $maxAttempts = 5;
        $isLocked = $this->userRepository->isLockedOut($username, $maxAttempts);

        if ($isLocked) {
            $html = $this->twig->render('admin/auth/error.html.twig', [
                'error_message'      => 'Acesso bloqueado por excesso de tentativas incorretas.',
                'is_locked'          => true,
                'username'           => $username,
                'remaining_attempts' => 0,
                'login_url'          => $routeParser->urlFor('admin.login.form'),
            ]);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8')->withStatus(403);
        }

        // Autentica via AdminAuthService
        $userData = $this->authService->authenticate($username, $password, $ip);

        if ($userData) {
            // Cria a sessão administrativa (Redis ou PHP Native Session)
            $sessionId = $this->authService->createSession($userData);

            // Define o Cookie de Sessão Administrativa de forma segura
            $cookieValue = sprintf(
                'admin_session_id=%s; Path=/; HttpOnly; SameSite=Lax; Max-Age=7200',
                $sessionId
            );

            $dashboardUrl = $routeParser->urlFor('admin.dashboard');
            
            return $response
                ->withHeader('Set-Cookie', $cookieValue)
                ->withHeader('Location', $dashboardUrl)
                ->withStatus(302);
        }

        // Falha no login: Calcula tentativas restantes e renderiza a tela de aviso
        $attempts = $this->userRepository->getLoginAttempts($username);
        $remaining = max(0, $maxAttempts - $attempts);

        $userExists = $this->userRepository->findByUsername($username);
        if ($userExists && !$userExists->isStatus()) {
            $errorMsg = 'Sua conta de administrador está inativa no sistema.';
        } else {
            $errorMsg = 'Aviso: Usuário e/ou senha inválidos.';
        }

        $html = $this->twig->render('admin/auth/error.html.twig', [
            'error_message'      => $errorMsg,
            'is_locked'          => ($remaining === 0),
            'username'           => $username,
            'remaining_attempts' => $remaining,
            'login_url'          => $routeParser->urlFor('admin.login.form'),
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8')->withStatus(401);
    }
}
