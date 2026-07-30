<?php

require __DIR__ . '/../../vendor/autoload.php';

use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\SecurityHeadersMiddleware;

$middleware = new SecurityHeadersMiddleware();

$dummyHandler = new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): Response {
        $res = new Response();
        $res->getBody()->write("OK");
        return $res;
    }
};

$requestFactory = new ServerRequestFactory();

echo "=== TESTE 1: Requisição HTTP Normal ===\n";
$reqHttp = $requestFactory->createServerRequest('GET', 'http://localhost/pt-br/login');
$res1 = $middleware->process($reqHttp, $dummyHandler);

$expectedHeaders = [
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-Content-Type-Options' => 'nosniff',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
];

$allPassed = true;
foreach ($expectedHeaders as $header => $expectedValue) {
    $actual = $res1->getHeaderLine($header);
    if ($actual === $expectedValue) {
        echo "✅ [PASS] $header: $actual\n";
    } else {
        echo "❌ [FAIL] $header - Esperado: '$expectedValue', Obtido: '$actual'\n";
        $allPassed = false;
    }
}

$csp = $res1->getHeaderLine('Content-Security-Policy');
if (!empty($csp) && str_contains($csp, "default-src 'self'")) {
    echo "✅ [PASS] Content-Security-Policy: Presente e válido\n";
} else {
    echo "❌ [FAIL] Content-Security-Policy: Ausente ou inválido\n";
    $allPassed = false;
}

$hstsHttp = $res1->getHeaderLine('Strict-Transport-Security');
if (empty($hstsHttp)) {
    echo "✅ [PASS] Strict-Transport-Security: Ausente em HTTP (Correto)\n";
} else {
    echo "❌ [FAIL] Strict-Transport-Security: Não deveria existir em HTTP\n";
    $allPassed = false;
}

echo "\n=== TESTE 2: Requisição HTTPS Segura ===\n";
$reqHttps = $requestFactory->createServerRequest('GET', 'https://localhost/pt-br/login');
$res2 = $middleware->process($reqHttps, $dummyHandler);

$hstsHttps = $res2->getHeaderLine('Strict-Transport-Security');
if (!empty($hstsHttps) && str_contains($hstsHttps, 'max-age=31536000')) {
    echo "✅ [PASS] Strict-Transport-Security em HTTPS: $hstsHttps\n";
} else {
    echo "❌ [FAIL] Strict-Transport-Security em HTTPS: Ausente ou inválido ('$hstsHttps')\n";
    $allPassed = false;
}

echo "\n=========================================\n";
if ($allPassed) {
    echo "🎉 TODOS OS TESTES DE SECURITY HEADERS PASSARAM!\n";
} else {
    echo "⚠️ ALGUNS TESTES FALHARAM.\n";
    exit(1);
}
