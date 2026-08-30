<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Alpha\Controller\Actions\Customer\Auth\RegisterAction;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Auth\Services\CustomerAuthService;
use Containers\AppBootstrap;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

#[CoversClass(RegisterAction::class)]
class CustomerAutoLoginRegisterTest extends TestCase
{
    private ServerRequestFactory $requestFactory;
    private $container;
    private CustomerRepository $customerRepository;
    private CustomerAuthService $authService;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $bootstrap = AppBootstrap::boot();
        $this->container = $bootstrap->getContainer();
        $this->requestFactory = new ServerRequestFactory();

        $this->customerRepository = $this->container->get(CustomerRepository::class);
        $this->authService = $this->container->get(CustomerAuthService::class);

        $_SESSION = [];
        $_COOKIE = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_COOKIE = [];
    }

    /**
     * Teste 1: Cadastro válido cria a conta, inicia a sessão (Auto-Login) e retorna cookie Set-Cookie.
     */
    public function testRegisterActionPerformsAutoLoginAndEmitsSessionCookie(): void
    {
        $action = new RegisterAction($this->customerRepository, $this->authService);

        $uniqueEmail = 'autologin.test.' . microtime(true) . '@exemplo.com';
        $params = [
            'firstname' => 'Carlos',
            'lastname'  => 'Silva',
            'email'     => $uniqueEmail,
            'telephone' => '11999998888',
            'password'  => 'SenhaForte123!',
            'confirm'   => 'SenhaForte123!',
            'agree'     => '1'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/cadastro')
            ->withAttribute('lang', 'pt-br')
            ->withParsedBody($params);

        $response = new Response();
        $res = $action($request, $response, []);

        $this->assertSame(200, $res->getStatusCode());
        $this->assertTrue($res->hasHeader('Set-Cookie'));
        $cookieHeader = $res->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('session_id=', $cookieHeader);

        // Valida que o JSON contém os campos de sucesso
        $body = (string)$res->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
        $this->assertGreaterThan(0, $data['customer_id']);
        $this->assertNotEmpty($data['redirect']);

        // Valida sincronização da sessão
        $this->assertSame($data['customer_id'], (int)$_SESSION['customer_id']);
        $this->assertSame($uniqueEmail, $_SESSION['customer_email']);
    }

    /**
     * Teste 2: Suporte a parâmetro redirect para retornar diretamente ao checkout após cadastro.
     */
    public function testRegisterActionSupportsCustomRedirect(): void
    {
        $action = new RegisterAction($this->customerRepository, $this->authService);

        $uniqueEmail = 'redirect.test.' . microtime(true) . '@exemplo.com';
        $params = [
            'firstname' => 'Ana',
            'lastname'  => 'Costa',
            'email'     => $uniqueEmail,
            'telephone' => '11988887777',
            'password'  => 'SenhaForte123!',
            'confirm'   => 'SenhaForte123!',
            'agree'     => '1',
            'redirect'  => '/pt-br/checkout'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/cadastro')
            ->withAttribute('lang', 'pt-br')
            ->withParsedBody($params);

        $response = new Response();
        $res = $action($request, $response, []);

        $this->assertSame(200, $res->getStatusCode());
        $body = (string)$res->getBody();
        $data = json_decode($body, true);

        $this->assertSame('/pt-br/checkout', $data['redirect']);
        $this->assertSame($data['customer_id'], (int)$_SESSION['customer_id']);
    }

    /**
     * Teste 3: Validações de erro não criam sessão nem emitem cookie de login.
     */
    public function testRegisterActionFailsWithValidationErrors(): void
    {
        $action = new RegisterAction($this->customerRepository, $this->authService);

        $params = [
            'firstname' => '',
            'lastname'  => '',
            'email'     => 'email_invalido',
            'password'  => '123',
            'confirm'   => '456'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/cadastro')
            ->withAttribute('lang', 'pt-br')
            ->withParsedBody($params);

        $response = new Response();
        $res = $action($request, $response, []);

        $this->assertSame(400, $res->getStatusCode());
        $this->assertFalse($res->hasHeader('Set-Cookie'));

        $body = (string)$res->getBody();
        $data = json_decode($body, true);

        $this->assertArrayHasKey('error', $data);
        $this->assertNotEmpty($data['error']);
        $this->assertArrayNotHasKey('customer_id', $_SESSION);
    }
}
