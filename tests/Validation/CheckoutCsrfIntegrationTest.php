<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Alpha\Auth\Middleware\CsrfGuardMiddleware;
use Slim\Factory\AppFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CsrfGuardMiddleware::class)]
class CheckoutCsrfIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    public function testJsonCheckoutWithCsrfPassesValidation(): void
    {
        $app = AppFactory::create();
        $twigEnv = null;
        
        $app->add(new CsrfGuardMiddleware($twigEnv));
        $app->addBodyParsingMiddleware();

        $app->get('/pt-br/checkout', function (Request $request, Response $response) {
            $nameKey = $request->getAttribute('csrf_name_key', 'csrf_name');
            $valueKey = $request->getAttribute('csrf_value_key', 'csrf_value');
            $response->getBody()->write(json_encode([
                'nameKey'  => $nameKey,
                'valueKey' => $valueKey,
                'name'     => $request->getAttribute($nameKey),
                'value'    => $request->getAttribute($valueKey),
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $app->post('/pt-br/checkout', function (Request $request, Response $response) {
            $parsed = $request->getParsedBody();
            $response->getBody()->write(json_encode([
                'success' => true,
                'payment_firstname' => $parsed['payment_firstname'] ?? null
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });

        $requestFactory = new ServerRequestFactory();
        $streamFactory = new StreamFactory();

        // 1. GET para gerar tokens CSRF na sessão
        $getReq = $requestFactory->createServerRequest('GET', '/pt-br/checkout');
        $getRes = $app->handle($getReq);
        $tokenData = json_decode((string)$getRes->getBody(), true);

        $this->assertNotEmpty($tokenData['name'], "CSRF Name token deve ser gerado.");
        $this->assertNotEmpty($tokenData['value'], "CSRF Value token deve ser gerado.");

        // 2. POST com payload JSON contendo CSRF no body e headers
        $postData = [
            'payment_firstname' => 'João',
            'payment_lastname' => 'Silva',
            $tokenData['nameKey'] => $tokenData['name'],
            $tokenData['valueKey'] => $tokenData['value']
        ];

        $postReq = $requestFactory->createServerRequest('POST', '/pt-br/checkout')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-CSRF-Name', $tokenData['name'])
            ->withHeader('X-CSRF-Value', $tokenData['value'])
            ->withBody($streamFactory->createStream(json_encode($postData)));

        $postRes = $app->handle($postReq);

        $this->assertEquals(200, $postRes->getStatusCode(), "Requisição de checkout em JSON com CSRF válido deve ter status 200.");
        $resData = json_decode((string)$postRes->getBody(), true);
        $this->assertTrue($resData['success']);
        $this->assertEquals('João', $resData['payment_firstname']);
    }

    public function testApiRoutesBypassCsrfValidation(): void
    {
        $app = AppFactory::create();
        $twigEnv = null;

        $app->add(new CsrfGuardMiddleware($twigEnv));
        $app->addBodyParsingMiddleware();

        $app->post('/api/carrinho/dados', function (Request $request, Response $response) {
            $parsed = $request->getParsedBody();
            $response->getBody()->write(json_encode([
                'success' => true,
                'items_count' => count($parsed['items'] ?? [])
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });

        $requestFactory = new ServerRequestFactory();
        $streamFactory = new StreamFactory();

        // Envia POST sem nenhum token CSRF
        $postReq = $requestFactory->createServerRequest('POST', '/api/carrinho/dados')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream(json_encode([
                'items' => [['product_id' => 1, 'quantity' => 1]]
            ])));

        $postRes = $app->handle($postReq);

        $this->assertEquals(200, $postRes->getStatusCode(), "Requisições para /api/* não devem ser bloqueadas pelo CsrfGuardMiddleware.");
        $resData = json_decode((string)$postRes->getBody(), true);
        $this->assertTrue($resData['success']);
        $this->assertEquals(1, $resData['items_count']);
    }
}
