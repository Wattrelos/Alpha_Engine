<?php

require __DIR__ . '/../../vendor/autoload.php';

use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

$requestFactory = new ServerRequestFactory();

// Dummy Handler que dispara uma exceção proposital
$failingHandler = new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): Response {
        throw new \RuntimeException("FALHA_INTERNA_DE_BANCO_E_SISTEMA_NA_LINHA_123");
    }
};

echo "=== TESTE 1: Ambiente de Desenvolvimento (APP_ENV=development, APP_DEBUG=true) ===\n";

$appDev = AppFactory::create();
$errorMiddlewareDev = $appDev->addErrorMiddleware(true, true, true);

$reqDev = $requestFactory->createServerRequest('GET', '/teste-erro');
$resDev = $errorMiddlewareDev->process($reqDev, $failingHandler);

$bodyDev = (string)$resDev->getBody();
echo "Status Code Dev: " . $resDev->getStatusCode() . "\n";
$hasTraceDev = str_contains($bodyDev, 'FALHA_INTERNA_DE_BANCO_E_SISTEMA');

if ($resDev->getStatusCode() === 500 && $hasTraceDev) {
    echo "✅ [PASS] Modo Desenvolvimento exibe detalhes da exceção para os desenvolvedores.\n";
} else {
    echo "❌ [FAIL] Inconsistência no ErrorMiddleware em modo de desenvolvimento.\n";
    exit(1);
}

echo "\n=== TESTE 2: Ambiente de Produção (APP_ENV=production, APP_DEBUG=false) ===\n";

$appProd = AppFactory::create();
$errorMiddlewareProd = $appProd->addErrorMiddleware(false, true, true);

$twigEnv = new \Twig\Environment(new \Twig\Loader\ArrayLoader([]));

// Registra o Handler 500 idêntico ao index.php de produção
$errorMiddlewareProd->setDefaultErrorHandler(
    function ($request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($twigEnv) {
        $response = new \Slim\Psr7\Response();

        $isXmlHttpRequest = strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
        $acceptsJson = str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json');

        if ($isXmlHttpRequest || $acceptsJson) {
            $response->getBody()->write((string)json_encode([
                'error' => [
                    'warning' => 'Ocorreu um erro interno no servidor ao processar sua requisição.'
                ]
            ], JSON_UNESCAPED_UNICODE));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }

        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>500 - Erro Interno no Servidor</title></head><body><h1>500 - Erro Interno no Servidor</h1><p>Ocorreu um erro inesperado.</p></body></html>';
        $response->getBody()->write($html);
        return $response->withStatus(500);
    }
);

$reqProd = $requestFactory->createServerRequest('GET', '/teste-erro');
$resProd = $errorMiddlewareProd->process($reqProd, $failingHandler);

$bodyProd = (string)$resProd->getBody();
echo "Status Code Prod: " . $resProd->getStatusCode() . "\n";
echo "Body Prod Snippet: " . substr($bodyProd, 0, 120) . "\n";

$hasTraceProd = str_contains($bodyProd, 'FALHA_INTERNA_DE_BANCO_E_SISTEMA');

if ($resProd->getStatusCode() === 500 && !$hasTraceProd) {
    echo "✅ [PASS] Modo Produção oculta detalhes e arquivos de exceção, exibindo resposta 500 amigável.\n";
} else {
    echo "❌ [FAIL] Detalhes de exceção vazaram em modo de produção!\n";
    exit(1);
}

echo "\n=== TESTE 3: Erro 500 em Requisição AJAX Produção ===\n";

$reqAjaxProd = $requestFactory->createServerRequest('POST', '/api/teste')
    ->withHeader('X-Requested-With', 'XMLHttpRequest');
$resAjaxProd = $errorMiddlewareProd->process($reqAjaxProd, $failingHandler);

$bodyAjaxProd = (string)$resAjaxProd->getBody();
echo "Status Code AJAX Prod: " . $resAjaxProd->getStatusCode() . "\n";
echo "JSON Body AJAX Prod: " . $bodyAjaxProd . "\n";

if ($resAjaxProd->getStatusCode() === 500 && str_contains($bodyAjaxProd, 'erro interno no servidor') && !str_contains($bodyAjaxProd, 'FALHA_INTERNA')) {
    echo "✅ [PASS] Erro 500 em AJAX retorna JSON limpo sem stack trace.\n";
} else {
    echo "❌ [FAIL] Falha no tratamento de exceção 500 para AJAX em produção.\n";
    exit(1);
}

echo "\n=========================================\n";
echo "🎉 TODOS OS TESTES DE DEBUG MODE E ERRO 500 PASSARAM!\n";
