<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\SignatureMiddleware;

class WebhookSignatureTest extends TestCase
{
    private ServerRequestFactory $requestFactory;
    private RequestHandlerInterface $nextHandler;
    private string $apiSecret;

    protected function setUp(): void
    {
        $this->requestFactory = new ServerRequestFactory();
        $this->apiSecret = $_ENV['API_SIGNATURE_SECRET'] ?? 'sua_chave_secreta_e_muito_longa_123';
        $this->nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write(json_encode(['status' => 'webhook_processed']));
                return $res->withStatus(200);
            }
        };
    }

    /**
     * Teste 1: Requisição de Webhook sem o cabeçalho X-Signature DEVE ser rejeitada com HTTP 401.
     */
    public function testMissingSignatureHeaderIsRejected(): void
    {
        $middleware = new SignatureMiddleware();
        $payload = json_encode(['event' => 'payment_intent.succeeded', 'amount' => 15000]);

        $request = $this->requestFactory->createServerRequest('POST', '/api/webhook/stripe')
            ->withParsedBody(json_decode($payload, true));
        $request->getBody()->write($payload);

        $response = $middleware($request, $this->nextHandler);

        $this->assertEquals(401, $response->getStatusCode(), "Webhook sem cabeçalho X-Signature deve ser rejeitado com HTTP 401.");
        $body = (string)$response->getBody();
        $this->assertStringContainsString('ausente', $body);
    }

    /**
     * Teste 2: Webhook com assinatura HMAC adulterada/inválida DEVE ser rejeitada com HTTP 401.
     */
    public function testInvalidHmacSignatureIsRejected(): void
    {
        $middleware = new SignatureMiddleware();
        $payload = json_encode(['event' => 'charge.refunded', 'amount' => 5000]);

        $invalidSignature = 'bad_hash_' . md5('tampered_signature');

        $request = $this->requestFactory->createServerRequest('POST', '/api/webhook/stripe')
            ->withHeader('X-Signature', $invalidSignature);
        $request->getBody()->write($payload);

        $response = $middleware($request, $this->nextHandler);

        $this->assertEquals(401, $response->getStatusCode(), "Assinatura HMAC falsa ou adulterada deve retornar HTTP 401.");
    }

    /**
     * Teste 3: Webhook com assinatura HMAC-SHA256 válida DEVE ser processado com sucesso (HTTP 200).
     */
    public function testValidHmacSignatureIsAccepted(): void
    {
        $middleware = new SignatureMiddleware();
        $payload = json_encode(['event' => 'checkout.session.completed', 'customer_id' => 'cus_999']);

        $validSignature = hash_hmac('sha256', $payload, $this->apiSecret);

        $request = $this->requestFactory->createServerRequest('POST', '/api/webhook/stripe')
            ->withHeader('X-Signature', $validSignature);
        $request->getBody()->write($payload);

        $response = $middleware($request, $this->nextHandler);

        $this->assertEquals(200, $response->getStatusCode(), "Assinatura HMAC válida deve autorizar o processamento do webhook.");
        $this->assertStringContainsString('webhook_processed', (string)$response->getBody());
    }
}
