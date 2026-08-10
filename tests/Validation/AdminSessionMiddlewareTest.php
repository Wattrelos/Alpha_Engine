<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\AdminSessionMiddleware;

#[CoversClass(AdminSessionMiddleware::class)]
class AdminSessionMiddlewareTest extends TestCase
{
    private ServerRequestFactory $requestFactory;
    private RequestHandlerInterface $nextHandler;

    protected function setUp(): void
    {
        $this->requestFactory = new ServerRequestFactory();
        $this->nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write("OK_ADMIN_PANEL");
                return $res->withStatus(200);
            }
        };
    }

    /**
     * Teste 1: Acesso a rotas administrativas protegidas sem sessão ativa deve ser bloqueado/redirecionado (302/401/403).
     */
    public function testUnauthenticatedAccessIsBlocked(): void
    {
        $middleware = new AdminSessionMiddleware();
        $request = $this->requestFactory->createServerRequest('GET', '/admin/dashboard');

        // Executa o middleware sem qualquer cookie ou id de sessão
        $response = $middleware($request, $this->nextHandler);

        $statusCode = $response->getStatusCode();
        
        // Deve retornar redirecionamento (302 para tela de login) ou 401/403
        $this->assertContains($statusCode, [302, 401, 403], "Acesso sem sessão deve ser bloqueado com HTTP 302, 401 ou 403.");
        if ($statusCode === 302) {
            $this->assertTrue($response->hasHeader('Location'), "Redirecionamento deve incluir cabeçalho Location.");
        }
    }

    /**
     * Teste 2: Sessão expirada ou id de sessão inválido deve ser barrada.
     */
    public function testInvalidSessionIdIsBlocked(): void
    {
        $middleware = new AdminSessionMiddleware();
        $request = $this->requestFactory->createServerRequest('GET', '/admin/product/list')
            ->withCookieParams(['admin_session_id' => 'session_id_inexistente_12345']);

        $response = $middleware($request, $this->nextHandler);

        $this->assertContains($response->getStatusCode(), [302, 401, 403]);
        $this->assertNotEquals("OK_ADMIN_PANEL", (string)$response->getBody());
    }
}
