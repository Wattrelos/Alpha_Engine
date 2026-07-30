<?php

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\RateLimitMiddleware;

$dummyHandler = new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): Response {
        $res = new Response();
        $res->getBody()->write("OK");
        return $res;
    }
};

$requestFactory = new ServerRequestFactory();

// Instancia um RateLimiter de teste com cota baixa (3 requisições por minuto)
$maxRequests = 3;
$decaySeconds = 60;
$testGroup = 'test_' . uniqid();
$middleware = new RateLimitMiddleware($maxRequests, $decaySeconds, $testGroup);

echo "=== TESTE 1: Envio de 3 requisições válidas dentro da cota ===\n";
for ($i = 1; $i <= $maxRequests; $i++) {
    $req = $requestFactory->createServerRequest('POST', '/login', ['REMOTE_ADDR' => '192.168.1.100']);
    $res = $middleware->process($req, $dummyHandler);

    $limit = $res->getHeaderLine('X-RateLimit-Limit');
    $remaining = $res->getHeaderLine('X-RateLimit-Remaining');
    $status = $res->getStatusCode();

    echo "Requisição #{$i} -> Status: {$status} | Limit: {$limit} | Remaining: {$remaining}\n";

    if ($status !== 200 || (int)$remaining !== ($maxRequests - $i)) {
        echo "❌ [FAIL] Inconsistência nos cabeçalhos de controle da requisição #{$i}\n";
        exit(1);
    }
}
echo "✅ [PASS] Todas as 3 requisições foram aceitas com a contagem de cota correta.\n";

echo "\n=== TESTE 2: 4ª Requisição (Deve exceder a cota e retornar HTTP 429) ===\n";
$reqOver = $requestFactory->createServerRequest('POST', '/login', ['REMOTE_ADDR' => '192.168.1.100'])
    ->withHeader('X-Requested-With', 'XMLHttpRequest');
$resOver = $middleware->process($reqOver, $dummyHandler);

$statusOver = $resOver->getStatusCode();
$retryAfter = $resOver->getHeaderLine('Retry-After');
$remainingOver = $resOver->getHeaderLine('X-RateLimit-Remaining');
$bodyOver = (string)$resOver->getBody();

echo "Status Code: {$statusOver}\n";
echo "Retry-After Header: {$retryAfter}s\n";
echo "X-RateLimit-Remaining Header: {$remainingOver}\n";
echo "JSON Body: {$bodyOver}\n";

if ($statusOver === 429 && !empty($retryAfter) && (str_contains($bodyOver, 'Muitas') || str_contains($bodyOver, 'warning'))) {
    echo "✅ [PASS] Requisição bloqueada com HTTP 429 Too Many Requests, Retry-After e resposta JSON válida.\n";
} else {
    echo "❌ [FAIL] Falha no bloqueio por limitação de taxa.\n";
    exit(1);
}

echo "\n=== TESTE 3: Verificação de Isolamento por IP diferente ===\n";
$reqOtherIp = $requestFactory->createServerRequest('POST', '/login', ['REMOTE_ADDR' => '10.0.0.50']);
$resOtherIp = $middleware->process($reqOtherIp, $dummyHandler);

$statusOther = $resOtherIp->getStatusCode();
$remainingOther = $resOtherIp->getHeaderLine('X-RateLimit-Remaining');

echo "Outro IP -> Status: {$statusOther} | Remaining: {$remainingOther}\n";

if ($statusOther === 200 && (int)$remainingOther === ($maxRequests - 1)) {
    echo "✅ [PASS] IP diferente possui sua própria cota isolada independente.\n";
} else {
    echo "❌ [FAIL] Falha no isolamento por endereço de IP.\n";
    exit(1);
}

echo "\n=========================================\n";
echo "🎉 TODOS OS TESTES DE RATE LIMITING PASSARAM!\n";
