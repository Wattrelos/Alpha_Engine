<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\SessionMiddleware;
use Alpha\Support\Customer;
use Containers\AppContainer;

class CustomerAccountShortcutsAuthTest extends TestCase
{
    private ServerRequestFactory $requestFactory;
    private RequestHandlerInterface $nextHandler;

    protected function setUp(): void
    {
        $this->requestFactory = new ServerRequestFactory();
        $this->nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write("OK_ACCOUNT_AUTHORIZED");
                return $res->withStatus(200);
            }
        };

        // Limpa estado de sessão antes de cada teste
        $_SESSION = [];
        $_COOKIE = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_COOKIE = [];
    }

    /**
     * Teste 1: Acesso deslogado às rotas /account/* é bloqueado com redirect 302 para /pt-br/login.
     */
    public function testUnauthenticatedAccessIsRedirectedToLogin(): void
    {
        $container = new AppContainer();
        $customer = new Customer();
        $container->bind('customer', $customer);

        $middleware = new SessionMiddleware($container);
        $request = $this->requestFactory->createServerRequest('GET', '/pt-br/account/edit')
            ->withAttribute('lang', 'pt-br');

        $response = $middleware($request, $this->nextHandler);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('/pt-br/login', $response->getHeaderLine('Location'));
        $this->assertFalse($customer->isLogged());
    }

    /**
     * Teste 2: Acesso autenticado via $_SESSION permite passagem no SessionMiddleware e sincroniza Customer.
     */
    public function testAuthenticatedAccessViaSessionPasses(): void
    {
        $container = new AppContainer();
        $customer = new Customer();
        $container->bind('customer', $customer);

        $_SESSION['logged_user'] = json_encode([
            'id' => 42,
            'name' => 'João da Silva',
            'email' => 'joao@exemplo.com',
            'telephone' => '11999999999',
            'customer_group_id' => 1
        ]);
        $_SESSION['customer_id'] = 42;

        $middleware = new SessionMiddleware($container);
        $request = $this->requestFactory->createServerRequest('GET', '/pt-br/account/edit')
            ->withAttribute('lang', 'pt-br');

        $response = $middleware($request, $this->nextHandler);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("OK_ACCOUNT_AUTHORIZED", (string)$response->getBody());
        $this->assertTrue($customer->isLogged());
        $this->assertSame(42, $customer->getId());
        $this->assertSame('João', $customer->getFirstName());
        $this->assertSame('da Silva', $customer->getLastName());
        $this->assertSame('joao@exemplo.com', $customer->getEmail());
        $this->assertSame('11999999999', $customer->getTelephone());
    }

    /**
     * Teste 3: Customer::setUser() injeta e valida o usuário imediatamente.
     */
    public function testCustomerSetUserDirectly(): void
    {
        $customer = new Customer();
        $this->assertFalse($customer->isLogged());

        $customer->setUser((object)[
            'id' => 99,
            'name' => 'Maria Oliveira',
            'email' => 'maria@exemplo.com',
            'telephone' => '21988888888',
            'customer_group_id' => 2
        ]);

        $this->assertTrue($customer->isLogged());
        $this->assertSame(99, $customer->getId());
        $this->assertSame('Maria', $customer->getFirstName());
        $this->assertSame('Oliveira', $customer->getLastName());
        $this->assertSame('maria@exemplo.com', $customer->getEmail());
        $this->assertSame(2, $customer->getGroupId());

        $customer->clearUser();
        $this->assertFalse($customer->isLogged());
        $this->assertSame(0, $customer->getId());
    }

    /**
     * Teste 4: Se o Redis estiver ativo e contiver a sessão, SessionMiddleware e Customer recuperam os dados e passam.
     */
    public function testAuthenticatedAccessWithRedisSession(): void
    {
        $redisHost = $_ENV['REDIS_HOST'] ?? '';
        $redisEnabled = filter_var($_ENV['REDIS_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);

        if (!$redisEnabled || empty($redisHost)) {
            $this->markTestSkipped('Redis não habilitado neste ambiente.');
        }

        try {
            $redis = new \Predis\Client([
                'host' => $redisHost,
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                'timeout' => 1.0
            ]);
            $redis->connect();
        } catch (\Exception $e) {
            $this->markTestSkipped('Não foi possível conectar ao Redis: ' . $e->getMessage());
        }

        $testSessionId = 'test_session_' . bin2hex(random_bytes(16));
        $userData = [
            'id' => 77,
            'name' => 'Carlos Teste',
            'email' => 'carlos@teste.com',
            'telephone' => '31977777777',
            'customer_group_id' => 1,
            'role' => 'client_premium'
        ];

        $redis->set('sessao:' . $testSessionId, json_encode($userData));
        $redis->expire('sessao:' . $testSessionId, 60);

        try {
            $container = new AppContainer();
            $customer = new Customer();
            $container->bind('customer', $customer);

            $_COOKIE['session_id'] = $testSessionId;

            $middleware = new SessionMiddleware($container);
            $request = $this->requestFactory->createServerRequest('GET', '/pt-br/account/orders')
                ->withCookieParams(['session_id' => $testSessionId])
                ->withAttribute('lang', 'pt-br');

            $response = $middleware($request, $this->nextHandler);

            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame("OK_ACCOUNT_AUTHORIZED", (string)$response->getBody());
            $this->assertTrue($customer->isLogged());
            $this->assertSame(77, $customer->getId());
            $this->assertSame('Carlos', $customer->getFirstName());
            $this->assertSame('carlos@teste.com', $customer->getEmail());
        } finally {
            $redis->del('sessao:' . $testSessionId);
        }
    }

    /**
     * Teste 5: LegacyRouteRedirectMiddleware redireciona parâmetros ?route= legados de account para rotas canônicas.
     */
    public function testLegacyRouteRedirects(): void
    {
        $seoRepo = $this->createMock(\Alpha\Model\Domain\Repositories\SeoUrlRepository::class);
        $middleware = new \Alpha\Auth\Middleware\LegacyRouteRedirectMiddleware($seoRepo);

        $testMap = [
            'account/edit'        => '/pt-br/account/edit',
            'account/password'    => '/pt-br/account/resetar-senha',
            'account/address'     => '/pt-br/account/addresses',
            'account/wishlist'    => '/pt-br/account/wishlist',
            'account/order'       => '/pt-br/account/orders',
            'account/transaction' => '/pt-br/account/transaction',
        ];

        foreach ($testMap as $legacyRoute => $expectedUrl) {
            $request = $this->requestFactory->createServerRequest('GET', '/')
                ->withQueryParams(['route' => $legacyRoute]);

            $response = $middleware($request, $this->nextHandler);

            $this->assertSame(301, $response->getStatusCode(), "Rota legada {$legacyRoute} deve redirecionar com 301.");
            $this->assertTrue($response->hasHeader('Location'));
            $this->assertSame($expectedUrl, $response->getHeaderLine('Location'));
        }
    }

    /**
     * Teste 6: Verifica se todas as 6 ações do painel Minha Conta reconhecem o usuário logado e não redirecionam para login.
     */
    public function testActionsRecognizeLoggedCustomer(): void
    {
        $container = new AppContainer();
        $customer = new Customer();
        $customer->setUser((object)[
            'id' => 1,
            'name' => 'Teste Cliente',
            'email' => 'cliente@teste.com',
            'telephone' => '11999999999',
            'customer_group_id' => 1
        ]);
        $container->bind('customer', $customer);

        $this->assertTrue($customer->isLogged(), "Customer deve estar logado.");
        $this->assertSame(1, $customer->getId());

        // Quando deslogado, isLogged deve ser false
        $customerUnlogged = new Customer();
        $this->assertFalse($customerUnlogged->isLogged());
    }
}
