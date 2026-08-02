<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Alpha\Support\EnvironmentManager;
use Alpha\Auth\Middleware\InstallationCheckMiddleware;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

echo "=== INICIANDO SUÍTE DE TESTES: TENANT PROVISIONING & SETUP ===\n\n";

// 1. Teste de Leitura e Parsing do EnvironmentManager
echo "[TEST 1] EnvironmentManager - Leitura e Parsing do .env.example / .env\n";
$testEnvFile = __DIR__ . '/../storage/cache/test_app.env';

if (file_exists($testEnvFile)) {
    @unlink($testEnvFile);
}

$envManager = new EnvironmentManager($testEnvFile);

// Estado Inicial: não instalado
echo "  isInstalled() inicial: " . ($envManager->isInstalled() ? 'true' : 'false') . "\n";
if ($envManager->isInstalled()) {
    echo "  [FAIL] Esperava isInstalled() == false para arquivo inexistente/zerado.\n";
    exit(1);
}
echo "  [PASS] isInstalled() respondeu corretamente para estado uninstalled.\n\n";

// 2. Teste de Escrita Atômica do EnvironmentManager
echo "[TEST 2] EnvironmentManager - Escrita Atômica e Geração de Chaves\n";
$key1 = EnvironmentManager::generateRandomKey(32);
$key2 = EnvironmentManager::generateRandomKey(32);

$updateResult = $envManager->updateEnv([
    'APP_ENV'        => 'development',
    'APP_INSTALLED'  => 'true',
    'DB_HOSTNAME'    => '127.0.0.1',
    'JWT_SECRET_KEY' => $key1,
]);

if (!$updateResult || !file_exists($testEnvFile)) {
    echo "  [FAIL] Falha ao gravar arquivo temporário do .env.\n";
    exit(1);
}

$parsed = $envManager->readEnv();
echo "  APP_INSTALLED lido do .env: " . ($parsed['APP_INSTALLED'] ?? '') . "\n";
echo "  JWT_SECRET_KEY gerada: " . substr($parsed['JWT_SECRET_KEY'] ?? '', 0, 10) . "...\n";

if (($parsed['APP_INSTALLED'] ?? '') !== 'true') {
    echo "  [FAIL] O valor de APP_INSTALLED não foi gravado corretamente.\n";
    exit(1);
}
echo "  [PASS] Escrita atômica do .env funcionou com sucesso.\n\n";

// 3. Teste do Middleware de Instalação (InstallationCheckMiddleware)
echo "[TEST 3] InstallationCheckMiddleware - Bloqueio de Rota /setup quando APP_INSTALLED=true\n";
$middleware = new InstallationCheckMiddleware($envManager);

// Simula Request HTTP para GET /setup
$requestFactory = new ServerRequestFactory();
$request = $requestFactory->createServerRequest('GET', '/setup');

// Dummy Handler que retornaria 200 OK se a requisição passasse
$dummyHandler = new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): Response {
        $res = new Response();
        $res->getBody()->write('OK');
        return $res->withStatus(200);
    }
};

$response = $middleware->process($request, $dummyHandler);
echo "  Status retornado para GET /setup com APP_INSTALLED=true: " . $response->getStatusCode() . "\n";

if ($response->getStatusCode() !== 403) {
    echo "  [FAIL] Esperava status 403 Forbidden para /setup quando já instalado, mas recebeu {$response->getStatusCode()}.\n";
    exit(1);
}
echo "  [PASS] Middleware bloqueou acesso ao /setup com status 403 Forbidden.\n\n";

// 4. Teste do Middleware com APP_INSTALLED=false (Redirecionamento para /setup)
echo "[TEST 4] InstallationCheckMiddleware - Redirecionamento 302 quando APP_INSTALLED=false\n";
@unlink($testEnvFile);
unset($_ENV['APP_INSTALLED']);
putenv('APP_INSTALLED');

$envManagerUninstalled = new EnvironmentManager($testEnvFile);
$middlewareUninstalled = new InstallationCheckMiddleware($envManagerUninstalled);

$requestHome = $requestFactory->createServerRequest('GET', '/');
$responseHome = $middlewareUninstalled->process($requestHome, $dummyHandler);

echo "  Status retornado para GET / com APP_INSTALLED=false: " . $responseHome->getStatusCode() . "\n";
echo "  Header Location: " . $responseHome->getHeaderLine('Location') . "\n";

if ($responseHome->getStatusCode() !== 302 || $responseHome->getHeaderLine('Location') !== '/setup') {
    echo "  [FAIL] Esperava redirecionamento 302 para /setup quando uninstalled.\n";
    exit(1);
}
echo "  [PASS] Redirecionamento 302 para /setup funcionou perfeitamente.\n\n";

// Limpeza de arquivos temporários do teste
@unlink($testEnvFile);

echo "=== TODOS OS TESTES DE TENANT PROVISIONING PASSARAM COM SUCESSO! ===\n";
