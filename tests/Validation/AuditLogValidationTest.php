<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Services\Audit\AuditLoggerService;
use Alpha\Auth\Middleware\RequestAuditMiddleware;
use Alpha\Auth\Middleware\AdminSessionMiddleware;
use Alpha\Events\QueueService;

#[CoversClass(AuditLoggerService::class)]
#[CoversClass(RequestAuditMiddleware::class)]
#[CoversClass(AdminSessionMiddleware::class)]
class AuditLogValidationTest extends TestCase
{
    private ServerRequestFactory $requestFactory;
    private AuditLoggerService $auditLogger;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'admin');
        }
        $configPath = __DIR__ . '/../../backend/config.php';
        if (file_exists($configPath)) {
            require_once $configPath;
        }

        $this->requestFactory = new ServerRequestFactory();
        
        $mockQueue = $this->createStub(QueueService::class);
        $mockQueue->method('publish')->willThrowException(new \RuntimeException("RabbitMQ offline"));
        $this->auditLogger = new AuditLoggerService($mockQueue);
    }

    /**
     * Teste 1: RequestAuditMiddleware intercepta requisições HTTP e registra metadados de IP, Navegador e Rota.
     */
    public function testRequestAuditMiddlewareCapturesVisitorMetadata(): void
    {
        $middleware = new RequestAuditMiddleware($this->auditLogger);

        $request = $this->requestFactory->createServerRequest('GET', '/pt-br/carrinho')
            ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36')
            ->withHeader('CF-Connecting-IP', '203.0.113.195');

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write("CART_PAGE");
                return $res->withStatus(200);
            }
        };

        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());

        // Valida no banco se o log foi inserido
        $logs = $this->auditLogger->getFilteredAuditLogs(1, 10, 0, '203.0.113.195');
        $this->assertNotEmpty($logs, "O log gerado pelo middleware para o IP 203.0.113.195 deve estar presente.");

        $lastLog = $logs[0];
        $this->assertEquals('203.0.113.195', $lastLog['ip']);
        $this->assertIsArray($lastLog['payload']);
        $this->assertEquals('GET', $lastLog['payload']['method']);
        $this->assertEquals('/pt-br/carrinho', $lastLog['payload']['path']);
        $this->assertEquals(200, $lastLog['payload']['status']);
        $this->assertEquals('Google Chrome', $lastLog['payload']['browser']);
        $this->assertEquals('Windows 10/11', $lastLog['payload']['os']);
        $this->assertEquals('Desktop', $lastLog['payload']['device']);
    }

    /**
     * Teste 2: RequestAuditMiddleware ignora arquivos estáticos (CSS, JS, imagens).
     */
    public function testRequestAuditMiddlewareSkipsStaticAssets(): void
    {
        $uniqueIp = '198.51.100.77';
        $middleware = new RequestAuditMiddleware($this->auditLogger);

        $request = $this->requestFactory->createServerRequest('GET', '/css/admin/admin.css')
            ->withHeader('CF-Connecting-IP', $uniqueIp);

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                return $res->withStatus(200);
            }
        };

        $middleware->process($request, $handler);

        // Não deve haver log gravado para a requisição de asset
        $logs = $this->auditLogger->getFilteredAuditLogs(1, 10, 0, $uniqueIp);
        $this->assertEmpty($logs, "Requisições de assets estáticos (.css) NÃO devem ser registradas na tabela de auditoria.");
    }

    /**
     * Teste 3: AuditLoggerService calcula estatísticas e contagens com filtros.
     */
    public function testAuditLoggerServiceStatsAndFiltering(): void
    {
        $uniqueEvent = 'CUSTOM_TEST_EVENT_' . time();
        $this->auditLogger->logEvent($uniqueEvent, ['test_key' => 'test_val'], 'test_user', '10.0.0.99', 1);

        // 1. Filtragem por evento específico
        $filtered = $this->auditLogger->getFilteredAuditLogs(1, 10, 0, null, $uniqueEvent);
        $this->assertCount(1, $filtered);
        $this->assertEquals($uniqueEvent, $filtered[0]['event']);

        // 2. Contagem total com filtro
        $count = $this->auditLogger->getTotalAuditLogsCount(1, null, $uniqueEvent);
        $this->assertEquals(1, $count);

        // 3. Busca por ID
        $logId = (int)$filtered[0]['id'];
        $fetched = $this->auditLogger->getAuditLogById($logId, 1);
        $this->assertNotNull($fetched);
        $this->assertEquals('test_user', $fetched['username']);
        $this->assertEquals('test_val', $fetched['payload']['test_key']);

        // 4. Estatísticas consolidadas
        $stats = $this->auditLogger->getAuditStats(1);
        $this->assertArrayHasKey('total_today', $stats);
        $this->assertArrayHasKey('unique_ips_24h', $stats);
        $this->assertArrayHasKey('browser_counts', $stats);
        $this->assertGreaterThanOrEqual(1, $stats['total_today']);
    }

    /**
     * Teste 4: Verificação RBAC - Acesso à rota de auditoria exige a permissão 'system/audit'.
     */
    public function testRbacProtectionOnAuditRoute(): void
    {
        $middleware = new AdminSessionMiddleware();

        // 1. Grupo sem a permissão 'system/audit' (Grupo 2)
        $unauthorizedAdmin = (object)[
            'user_id'       => 10,
            'username'      => 'colaborador_vendas',
            'user_group_id' => 2
        ];

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['logged_admin'] = json_encode($unauthorizedAdmin);
        $_SESSION['logged_admin_expire'] = time() + 7200;

        $routeStub = $this->createStub(\Slim\Interfaces\RouteInterface::class);
        $routeStub->method('getName')->willReturn('admin.audit.list');

        $routeParserStub = $this->createStub(\Slim\Interfaces\RouteParserInterface::class);
        $dispatcherStub = $this->createStub(\Slim\Interfaces\DispatcherInterface::class);
        $routingResults = new \Slim\Routing\RoutingResults($dispatcherStub, 'GET', '/admin/auditoria', \Slim\Routing\RoutingResults::FOUND);

        $request = $this->requestFactory->createServerRequest('GET', '/admin/auditoria')
            ->withAttribute('logged_admin', $unauthorizedAdmin)
            ->withAttribute(\Slim\Routing\RouteContext::ROUTE, $routeStub)
            ->withAttribute(\Slim\Routing\RouteContext::ROUTE_PARSER, $routeParserStub)
            ->withAttribute(\Slim\Routing\RouteContext::ROUTING_RESULTS, $routingResults);

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write("ALLOWED");
                return $res->withStatus(200);
            }
        };

        $response = $middleware($request, $handler);
        $this->assertEquals(403, $response->getStatusCode(), "Usuário sem permissão 'system/audit' deve receber HTTP 403 Forbidden.");

        // 2. Super Administrator (user_group_id = 1) possui bypass automático e acessa com 200 OK
        $superAdmin = (object)[
            'user_id'       => 1,
            'username'      => 'admin',
            'user_group_id' => 1
        ];

        $requestSuper = $request->withAttribute('logged_admin', $superAdmin);
        $responseSuper = $middleware($requestSuper, $handler);
        $this->assertEquals(200, $responseSuper->getStatusCode(), "Super Administrator (user_group_id = 1) DEVE ter acesso permitido (200 OK).");
    }
}
