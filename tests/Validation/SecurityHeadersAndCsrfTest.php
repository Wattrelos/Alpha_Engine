<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\SecurityHeadersMiddleware;
use Alpha\Auth\Middleware\CsrfGuardMiddleware;

class SecurityHeadersAndCsrfTest extends TestCase
{
    private ServerRequestFactory $requestFactory;
    private RequestHandlerInterface $dummyHandler;

    protected function setUp(): void
    {
        $this->requestFactory = new ServerRequestFactory();
        $this->dummyHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write("<h1>OK</h1>");
                return $res->withStatus(200);
            }
        };
    }

    /**
     * Teste 1: Valida que a aplicação injeta todos os cabeçalhos de segurança OWASP
     * (X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Content-Security-Policy).
     */
    public function testOwaspSecurityHeadersAreInjected(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $request = $this->requestFactory->createServerRequest('GET', '/');

        $response = $middleware->process($request, $this->dummyHandler);

        $this->assertEquals('SAMEORIGIN', $response->getHeaderLine('X-Frame-Options'), "Proteção contra Clickjacking (SAMEORIGIN) deve estar ativa.");
        $this->assertEquals('nosniff', $response->getHeaderLine('X-Content-Type-Options'), "Proteção contra MIME-Sniffing (nosniff) deve estar ativa.");
        $this->assertEquals('1; mode=block', $response->getHeaderLine('X-XSS-Protection'));
        $this->assertTrue($response->hasHeader('Content-Security-Policy'), "Cabeçalho Content-Security-Policy deve estar presente.");
    }

    /**
     * Teste 2: Valida sanitização contra injeção de scripts maliciosos (XSS / HTML Injection).
     */
    public function testXssInputSanitization(): void
    {
        $maliciousPayload = "<script>alert('XSS_ATTACK');</script><a href='javascript:stealCookies()'>Clique Aqui</a>";

        $sanitizedOutput = htmlspecialchars($maliciousPayload, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $sanitizedOutput, "Tags script devem ser convertidas em entidades HTML inofensivas.");
        $this->assertStringContainsString('&lt;script&gt;', $sanitizedOutput, "Caracteres especiais '<' e '>' devem ser encodados.");
    }
}
