<?php

require __DIR__ . '/../../vendor/autoload.php';

use Slim\Psr7\Factory\ServerRequestFactory;
use Alpha\Support\CookieHelper;

$requestFactory = new ServerRequestFactory();

echo "=== TESTE 1: Conexão HTTP Simples (sem Secure flag) ===\n";
$reqHttp = $requestFactory->createServerRequest('GET', 'http://localhost/login');
$cookieHttp = CookieHelper::makeCookieHeader($reqHttp, 'session_id', 'token12345', 7200);

echo "Set-Cookie Header: $cookieHttp\n";

$hasHttpOnly = str_contains($cookieHttp, 'HttpOnly');
$hasSameSite = str_contains($cookieHttp, 'SameSite=Lax');
$hasSecureHttp = str_contains($cookieHttp, 'Secure');

$passedHttp = $hasHttpOnly && $hasSameSite && !$hasSecureHttp;

if ($passedHttp) {
    echo "✅ [PASS] HTTP: Cookie possui HttpOnly e SameSite=Lax, E NÃO possui a flag Secure.\n";
} else {
    echo "❌ [FAIL] HTTP: Falha no formato do cookie HTTP.\n";
}

echo "\n=== TESTE 2: Conexão HTTPS Segura (deve incluir ; Secure) ===\n";
$reqHttps = $requestFactory->createServerRequest('GET', 'https://localhost/login');
$cookieHttps = CookieHelper::makeCookieHeader($reqHttps, 'session_id', 'token12345', 7200);

echo "Set-Cookie Header: $cookieHttps\n";

$hasSecureHttps = str_contains($cookieHttps, '; Secure');

if ($hasSecureHttps) {
    echo "✅ [PASS] HTTPS: Cookie possui a flag '; Secure' anexada corretamente.\n";
} else {
    echo "❌ [FAIL] HTTPS: Flag '; Secure' ausente em conexão segura.\n";
}

echo "\n=== TESTE 3: Expiração de Cookie (-1 / Logout) ===\n";
$reqLogout = $requestFactory->createServerRequest('GET', 'https://localhost/logout');
$cookieLogout = CookieHelper::makeCookieHeader($reqLogout, 'session_id', '', -1);

echo "Set-Cookie Header: $cookieLogout\n";

$hasExpire = str_contains($cookieLogout, 'Expires=Thu, 01 Jan 1970');
if ($hasExpire && str_contains($cookieLogout, '; Secure')) {
    echo "✅ [PASS] Logout HTTPS: Cookie expirado com flag '; Secure'.\n";
} else {
    echo "❌ [FAIL] Logout HTTPS: Formato incorreto de expiração.\n";
}

echo "\n=========================================\n";
if ($passedHttp && $hasSecureHttps && $hasExpire) {
    echo "🎉 TODOS OS TESTES DE COOKIE SECURE PASSARAM!\n";
} else {
    echo "⚠️ ALGUNS TESTES FALHARAM.\n";
    exit(1);
}
