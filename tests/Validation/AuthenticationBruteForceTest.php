<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\RateLimitMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RateLimitMiddleware::class)]
class AuthenticationBruteForceTest extends TestCase
{
    private ServerRequestFactory $requestFactory;
    private RequestHandlerInterface $dummyHandler;

    protected function setUp(): void
    {
        $this->requestFactory = new ServerRequestFactory();
        $this->dummyHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write(json_encode(['status' => 'success']));
                return $res->withStatus(200);
            }
        };
    }

    /**
     * Teste 1: 5 tentativas consecutivas dentro da cota devem ser aceitas (Status 200).
     * A 6ª tentativa consecutiva deve ser bloqueada por Brute Force com HTTP 429 Too Many Requests.
     */
    public function testBruteForceLockoutAfterMaxAttempts(): void
    {
        $maxRequests = 5;
        $decaySeconds = 60;
        $testGroup = 'login_test_' . uniqid();
        $middleware = new RateLimitMiddleware($maxRequests, $decaySeconds, $testGroup);
        $clientIp = '192.168.1.50';

        // Dispara 5 tentativas dentro da cota
        for ($i = 1; $i <= $maxRequests; $i++) {
            $req = $this->requestFactory->createServerRequest('POST', '/login', ['REMOTE_ADDR' => $clientIp]);
            $res = $middleware->process($req, $this->dummyHandler);

            $this->assertEquals(200, $res->getStatusCode(), "Tentativa #{$i} deve ter retorno HTTP 200.");
            $this->assertEquals((string)($maxRequests - $i), $res->getHeaderLine('X-RateLimit-Remaining'));
        }

        // 6ª tentativa: Excede limite e deve retornar HTTP 429 com bloqueio por timeout
        $req6 = $this->requestFactory->createServerRequest('POST', '/login', ['REMOTE_ADDR' => $clientIp])
            ->withHeader('X-Requested-With', 'XMLHttpRequest');
        $res6 = $middleware->process($req6, $this->dummyHandler);

        $this->assertEquals(429, $res6->getStatusCode(), "A 6ª tentativa excede a cota e deve ser bloqueada com HTTP 429.");
        $this->assertTrue($res6->hasHeader('Retry-After'), "A resposta do bloqueio deve conter o cabeçalho Retry-After.");
        $this->assertGreaterThan(0, (int)$res6->getHeaderLine('Retry-After'));
        
        $body = (string)$res6->getBody();
        $this->assertStringContainsString('Muitas', $body, "O corpo da resposta deve alertar sobre excesso de tentativas.");
    }

    /**
     * Teste 2: Isolamento de Rate Limit por Endereço IP.
     * Bloquear IP A não afeta IP B.
     */
    public function testIpIsolationInRateLimiting(): void
    {
        $maxRequests = 2;
        $testGroup = 'login_ip_test_' . uniqid();
        $middleware = new RateLimitMiddleware($maxRequests, 60, $testGroup);

        // IP A esgota cota
        for ($i = 0; $i < $maxRequests; $i++) {
            $req = $this->requestFactory->createServerRequest('POST', '/login', ['REMOTE_ADDR' => '10.0.0.1']);
            $middleware->process($req, $this->dummyHandler);
        }

        // Tentativa extra do IP A -> Bloqueada
        $reqA = $this->requestFactory->createServerRequest('POST', '/login', ['REMOTE_ADDR' => '10.0.0.1']);
        $resA = $middleware->process($reqA, $this->dummyHandler);
        $this->assertEquals(429, $resA->getStatusCode());

        // Requisição vinda do IP B -> Deve ser autorizada normalmente
        $reqB = $this->requestFactory->createServerRequest('POST', '/login', ['REMOTE_ADDR' => '10.0.0.2']);
        $resB = $middleware->process($reqB, $this->dummyHandler);
        $this->assertEquals(200, $resB->getStatusCode(), "IP B deve possuir cota independente do IP A.");
    }
}
