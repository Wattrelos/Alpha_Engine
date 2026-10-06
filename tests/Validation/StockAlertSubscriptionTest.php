<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Alpha\Controller\Actions\Product\SubscribeStockAlertAction;
use Alpha\Controller\Actions\Product\UnsubscribeStockAlertAction;
use Alpha\Model\Domain\Repositories\StockAlertRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Events\StockReplenishedEvent;
use Alpha\Events\StockReplenishedListener;
use Alpha\Events\QueueService;
use Alpha\Services\Notification\StockAlertService;
use Containers\AppContainer;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\ArrayLoader;

class StockAlertSubscriptionTest extends TestCase
{
    private ServerRequestFactory $requestFactory;

    protected function setUp(): void
    {
        $this->requestFactory = new ServerRequestFactory();
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testSuccessfulSubscription(): void
    {
        $container = new AppContainer();

        $stockAlertRepo = $this->createMock(StockAlertRepository::class);
        $stockAlertRepo->expects($this->once())
            ->method('subscribe')
            ->willReturn(123);

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->expects($this->once())
            ->method('getProduct')
            ->with(10)
            ->willReturn(['id' => 10, 'name' => 'Colchão Queen Size AG Sonhos']);

        $action = new SubscribeStockAlertAction($stockAlertRepo, $productRepo, $container);

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/catalog/stock-alert/subscribe')
            ->withParsedBody([
                'product_id'       => 10,
                'name'             => 'Maria Oliveira',
                'email'            => 'maria.oliveira@teste.com',
                'phone'            => '11988887777',
                'consent_privacy'  => '1',
                'consent_marketing'=> '0'
            ]);

        $response = new Response();
        $result = $action($request, $response, []);

        $this->assertSame(200, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertStringContainsString('disponível', $body['message']);
    }

    public function testHoneypotSilentlyDiscardsBotSubmission(): void
    {
        $container = new AppContainer();

        $stockAlertRepo = $this->createMock(StockAlertRepository::class);
        $stockAlertRepo->expects($this->never())->method('subscribe');

        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->expects($this->never())->method('getProduct');

        $action = new SubscribeStockAlertAction($stockAlertRepo, $productRepo, $container);

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/catalog/stock-alert/subscribe')
            ->withParsedBody([
                'form_check_company' => 'Bot Enterprise LLC', // Campo honeypot preenchido
                'product_id'         => 10,
                'name'               => 'Spam Bot',
                'email'              => 'spam@bot.com',
                'consent_privacy'    => '1'
            ]);

        $response = new Response();
        $result = $action($request, $response, []);

        $this->assertSame(200, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertTrue($body['success']);
    }

    public function testInvalidEmailReturnsUnprocessableEntity(): void
    {
        $container = new AppContainer();

        $stockAlertRepo = $this->createMock(StockAlertRepository::class);
        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->method('getProduct')->willReturn(['id' => 10, 'name' => 'Produto']);

        $action = new SubscribeStockAlertAction($stockAlertRepo, $productRepo, $container);

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/catalog/stock-alert/subscribe')
            ->withParsedBody([
                'product_id'      => 10,
                'name'            => 'Cliente Teste',
                'email'           => 'email_invalido_sem_arroba',
                'consent_privacy' => '1'
            ]);

        $response = new Response();
        $result = $action($request, $response, []);

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertArrayHasKey('email', $body['errors']);
    }

    public function testMissingConsentPrivacyReturnsUnprocessableEntity(): void
    {
        $container = new AppContainer();

        $stockAlertRepo = $this->createMock(StockAlertRepository::class);
        $productRepo = $this->createMock(ProductRepository::class);
        $productRepo->method('getProduct')->willReturn(['id' => 10, 'name' => 'Produto']);

        $action = new SubscribeStockAlertAction($stockAlertRepo, $productRepo, $container);

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/catalog/stock-alert/subscribe')
            ->withParsedBody([
                'product_id'      => 10,
                'name'            => 'Cliente Teste',
                'email'           => 'cliente@teste.com',
                // consent_privacy ausente
            ]);

        $response = new Response();
        $result = $action($request, $response, []);

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertArrayHasKey('consent_privacy', $body['errors']);
    }

    public function testUnsubscribeWithValidToken(): void
    {
        $validToken = hash('sha256', 'token_teste_123');
        $stockAlertRepo = $this->createMock(StockAlertRepository::class);
        $stockAlertRepo->expects($this->once())
            ->method('unsubscribe')
            ->with($validToken)
            ->willReturn(true);

        $twig = new TwigEnvironment(new ArrayLoader([]));
        $action = new UnsubscribeStockAlertAction($stockAlertRepo, $twig);

        $request = $this->requestFactory->createServerRequest('GET', '/pt-br/catalog/stock-alert/unsubscribe')
            ->withQueryParams(['token' => $validToken]);

        $response = new Response();
        $result = $action($request, $response, []);

        $this->assertSame(200, $result->getStatusCode());
        $body = (string)$result->getBody();
        $this->assertStringContainsString('Alerta Cancelado com Sucesso', $body);
    }

    public function testStockReplenishedEventAndQueuePublishing(): void
    {
        $queueService = $this->createMock(QueueService::class);
        $queueService->expects($this->once())
            ->method('publish')
            ->with(
                'notification.stock_alert',
                $this->callback(function (array $payload) {
                    return $payload['event'] === 'stock.replenished'
                        && $payload['product_id'] === 1042
                        && $payload['variant_id'] === 308
                        && $payload['new_quantity'] === 5;
                })
            );

        $listener = new StockReplenishedListener($queueService);
        $event = new StockReplenishedEvent(1042, 308, 5, 1);

        $listener->handle($event);
        $this->assertSame('stock.replenished', $event->getName());
    }

    public function testStockAlertServiceQuotaCalculation(): void
    {
        $container = new AppContainer();
        $stockAlertRepo = $this->createMock(StockAlertRepository::class);
        $productRepo = $this->createMock(ProductRepository::class);

        $productRepo->method('getProduct')
            ->willReturn(['id' => 1042, 'name' => 'Porcelanato Polido 60x60', 'slug' => 'porcelanato-60x60']);

        // Se chegam 2 unidades no estoque, a cota com multiplicador 3 deve solicitar no máximo 6 alertas
        $stockAlertRepo->expects($this->once())
            ->method('getPendingAlertsForReplenishment')
            ->with(1042, null, 2, 3.0)
            ->willReturn([
                ['id' => 1, 'name' => 'Lead 1', 'email' => 'lead1@teste.com', 'unsubscribe_token' => 'tok1'],
                ['id' => 2, 'name' => 'Lead 2', 'email' => 'lead2@teste.com', 'unsubscribe_token' => 'tok2'],
                ['id' => 3, 'name' => 'Lead 3', 'email' => 'lead3@teste.com', 'unsubscribe_token' => 'tok3']
            ]);

        $stockAlertRepo->expects($this->once())
            ->method('markAsSent')
            ->with([1, 2, 3]);

        $service = new StockAlertService($stockAlertRepo, $productRepo, $container);
        $result = $service->processReplenishment(1042, null, 2, 1, 3.0);

        $this->assertSame('success', $result['status']);
        $this->assertSame(3, $result['notified_count']);
        $this->assertSame([1, 2, 3], $result['sent_ids']);
    }
}
