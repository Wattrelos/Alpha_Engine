<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Alpha\Controller\Actions\Cart\CalculateVisitorCartAction;
use Alpha\Controller\Actions\Cart\SyncCartAction;
use Alpha\Controller\Actions\Cart\SaveShippingCepAction;
use Containers\AppBootstrap;
use Slim\Factory\AppFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CalculateVisitorCartAction::class)]
#[CoversClass(SyncCartAction::class)]
#[CoversClass(SaveShippingCepAction::class)]
class VisitorCartValidationTest extends TestCase
{
    private $app;
    private ServerRequestFactory $requestFactory;
    private StreamFactory $streamFactory;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $bootstrap = AppBootstrap::boot();
        $container = $bootstrap->getContainer();
        $seoUrlRepository = $bootstrap->getSeoUrlRepository();

        $twig = \Slim\Views\Twig::create(__DIR__ . '/../../backend/resources/views', ['cache' => false]);
        $twigEnv = $twig->getEnvironment();
        $twigEnv->addExtension(new \Alpha\Support\Twig\UrlExtension($seoUrlRepository));

        $container->bind(\Twig\Environment::class, $twigEnv);
        $container->bind(\Slim\Views\Twig::class, $twig);

        AppFactory::setContainer($container);
        $this->app = AppFactory::create();
        $this->app->add(new \Alpha\Auth\Middleware\CsrfGuardMiddleware($twigEnv));
        $this->app->addBodyParsingMiddleware();
        $this->app->addRoutingMiddleware();

        $this->app->post('/api/carrinho/dados', CalculateVisitorCartAction::class);
        $this->app->post('/api/carrinho/sincronizar', SyncCartAction::class);
        $this->app->post('/api/carrinho/salvar-cep', SaveShippingCepAction::class);
        $this->app->get('/{lang}/produto/{slug}', function ($req, $res) {
            return $res;
        })->setName('product.detail');

        $this->requestFactory = new ServerRequestFactory();
        $this->streamFactory = new StreamFactory();
    }

    public function testCalculateVisitorCartWithValidProductReturnsHydratedData(): void
    {
        $request = $this->requestFactory->createServerRequest('POST', '/api/carrinho/dados')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode([
                'items' => [
                    ['product_id' => 1, 'quantity' => 2, 'option' => []]
                ]
            ])));

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['products']);
        $this->assertEquals(1, $data['products'][0]['product_id']);
        $this->assertEquals(2, $data['products'][0]['quantity']);
        $this->assertNotEmpty($data['products'][0]['price']);
        $this->assertNotEmpty($data['products'][0]['total']);
        $this->assertNotEmpty($data['totals']);
    }

    public function testCalculateVisitorCartWithEmptyItemsReturnsEmptyList(): void
    {
        $request = $this->requestFactory->createServerRequest('POST', '/api/carrinho/dados')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode([
                'items' => []
            ])));

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);

        $this->assertTrue($data['success']);
        $this->assertEmpty($data['products']);
    }

    public function testSaveShippingCepActionPersistsInSession(): void
    {
        $request = $this->requestFactory->createServerRequest('POST', '/api/carrinho/salvar-cep')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode([
                'cep' => '01310-100',
                'via_cep' => [
                    'cep' => '01310-100',
                    'logradouro' => 'Avenida Paulista',
                    'bairro' => 'Bela Vista',
                    'localidade' => 'São Paulo',
                    'uf' => 'SP'
                ]
            ])));

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testSyncCartActionExecutesSuccessfully(): void
    {
        $request = $this->requestFactory->createServerRequest('POST', '/api/carrinho/sincronizar')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode([
                'items' => [
                    ['product_id' => 1, 'quantity' => 1, 'option' => []]
                ]
            ])));

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);
        $this->assertTrue($data['success']);
    }
}
