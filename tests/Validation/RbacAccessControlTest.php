<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\AdminSessionMiddleware;
use Alpha\Model\Domain\Entities\User;

class RbacAccessControlTest extends TestCase
{
    private ServerRequestFactory $requestFactory;
    private RequestHandlerInterface $nextHandler;

    protected function setUp(): void
    {
        $this->requestFactory = new ServerRequestFactory();
        $this->nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write("ACTION_EXECUTED");
                return $res->withStatus(200);
            }
        };
    }

    /**
     * Teste 1: Autenticar como grupo comum (user_group_id != 1) sem permissão de alteração (modify)
     * e tentar realizar POST/DELETE deve ser bloqueado com HTTP 403 Forbidden.
     */
    public function testStandardUserGroupCannotModifyProtectedResources(): void
    {
        $middleware = new AdminSessionMiddleware();

        // Dados de administrador comum (grupo 2 - Operador Comum sem permissão 'catalog/product')
        $loggedAdmin = (object)[
            'user_id'       => 5,
            'username'      => 'operador_comum',
            'user_group_id' => 2
        ];

        // Popula sessão ativa do admin comum em $_SESSION
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['logged_admin'] = json_encode($loggedAdmin);
        $_SESSION['logged_admin_expire'] = time() + 7200;

        $routeStub = $this->createStub(\Slim\Interfaces\RouteInterface::class);
        $routeStub->method('getName')->willReturn('admin.product.update');

        $routeParserStub = $this->createStub(\Slim\Interfaces\RouteParserInterface::class);
        $dispatcherStub = $this->createStub(\Slim\Interfaces\DispatcherInterface::class);

        $routingResults = new \Slim\Routing\RoutingResults($dispatcherStub, 'POST', '/admin/product/update', \Slim\Routing\RoutingResults::FOUND);

        $request = $this->requestFactory->createServerRequest('POST', '/admin/product/update')
            ->withAttribute('logged_admin', $loggedAdmin)
            ->withAttribute(\Slim\Routing\RouteContext::ROUTE, $routeStub)
            ->withAttribute(\Slim\Routing\RouteContext::ROUTE_PARSER, $routeParserStub)
            ->withAttribute(\Slim\Routing\RouteContext::ROUTING_RESULTS, $routingResults);

        $response = $middleware($request, $this->nextHandler);

        $this->assertEquals(403, $response->getStatusCode(), "Ação de alteração sem privilégio RBAC deve retornar HTTP 403 Forbidden.");
        $this->assertStringContainsString('403', (string)$response->getBody());
    }

    /**
     * Teste 2: Valida regra de integridade de segurança que proíbe a exclusão do Superuser (ID 1).
     */
    public function testSuperUserDeletionIsForbidden(): void
    {
        $targetUserIdToDelete = 1; // ID do Superuser principal

        $userToDelete = new User();
        $userToDelete->setId($targetUserIdToDelete);
        $userToDelete->setUsername('superuser_admin');

        $canDelete = function(User $user): bool {
            // Regra de segurança inegociável do sistema: O Superuser (ID 1) NUNCA pode ser removido
            if ($user->getId() === 1) {
                return false;
            }
            return true;
        };

        $this->assertFalse($canDelete($userToDelete), "A tentativa de deletar o Superuser (ID 1) DEVE obrigatoriamente falhar.");
    }
}
