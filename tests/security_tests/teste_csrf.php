<?php

require __DIR__ . '/../../vendor/autoload.php';

use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\CsrfGuardMiddleware;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$twigEnv = new \Twig\Environment(new \Twig\Loader\ArrayLoader([]));
$middleware = new CsrfGuardMiddleware($twigEnv);

// Dummy Handler final que retorna HTTP 200 OK
$dummyHandler = new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): Response
    {
        $res = new Response();
        $res->getBody()->write("SUCCESS_OK");
        return $res;
    }
};

$requestFactory = new ServerRequestFactory();

echo "--- TESTE 1: GET Request (Deve passar e gerar CSRF no Twig) ---\n";
$getReq = $requestFactory->createServerRequest('GET', '/login');
$res1 = $middleware->process($getReq, $dummyHandler);
echo "Status Code: " . $res1->getStatusCode() . "\n";
$csrfData = $twigEnv->getGlobals()['csrf'] ?? [];
echo "Generated CSRF Name Key: " . ($csrfData['keys']['name'] ?? '') . "\n";
echo "Generated CSRF Value Key: " . ($csrfData['keys']['value'] ?? '') . "\n";
echo "Generated CSRF Name: " . ($csrfData['name'] ?? '') . "\n";
echo "Generated CSRF Value: " . ($csrfData['value'] ?? '') . "\n\n";

$csrfNameKey = $csrfData['keys']['name'];
$csrfValueKey = $csrfData['keys']['value'];
$csrfName = $csrfData['name'];
$csrfValue = $csrfData['value'];

echo "--- TESTE 2: POST sem Token (Deve falhar com 400 Bad Request) ---\n";
$postReqNoToken = $requestFactory->createServerRequest('POST', '/login');
$res2 = $middleware->process($postReqNoToken, $dummyHandler);
echo "Status Code: " . $res2->getStatusCode() . "\n";
echo "Body snippet: " . substr((string)$res2->getBody(), 0, 100) . "...\n\n";

echo "--- TESTE 3: POST AJAX sem Token (Deve falhar com JSON status 400) ---\n";
$postReqAjaxNoToken = $requestFactory->createServerRequest('POST', '/login')
    ->withHeader('X-Requested-With', 'XMLHttpRequest');
$res3 = $middleware->process($postReqAjaxNoToken, $dummyHandler);
echo "Status Code: " . $res3->getStatusCode() . "\n";
echo "JSON Body: " . (string)$res3->getBody() . "\n\n";

echo "--- TESTE 4: POST com Token Válido (Deve passar com HTTP 200 OK) ---\n";
$postReqValid = $requestFactory->createServerRequest('POST', '/login')
    ->withParsedBody([
        $csrfNameKey => $csrfName,
        $csrfValueKey => $csrfValue,
        'email' => 'teste@agsonhos.com',
        'password' => '123456'
    ]);
$res4 = $middleware->process($postReqValid, $dummyHandler);
echo "Status Code: " . $res4->getStatusCode() . "\n";
echo "Body: " . (string)$res4->getBody() . "\n";
