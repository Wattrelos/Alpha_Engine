<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Alpha\Controller\Actions\Customer\Account\NewsletterAction;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Entities\Customer\Customer as CustomerEntity;
use Alpha\Support\Customer;
use Alpha\Support\Language as Translator;
use Containers\AppContainer;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\ArrayLoader;
use Slim\Routing\RouteParser;
use Slim\Routing\RouteContext;
use Slim\Interfaces\RouteParserInterface;

class NewsletterActionValidationTest extends TestCase
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

    public function testUnauthenticatedAccessRedirectsToLogin(): void
    {
        $container = new AppContainer();
        $customer = new Customer();
        $container->bind('customer', $customer);

        $customerRepo = $this->createMock(CustomerRepository::class);
        $twig = new TwigEnvironment(new ArrayLoader([]));
        $translator = new Translator('pt-br');

        $action = new NewsletterAction($customerRepo, $container, $twig, $translator);

        $request = $this->requestFactory->createServerRequest('GET', '/pt-br/account/newsletter')
            ->withAttribute('lang', 'pt-br');
        $response = new Response();

        $result = $action($request, $response, []);

        $this->assertSame(302, $result->getStatusCode());
        $this->assertSame('/pt-br/login', $result->getHeaderLine('Location'));
    }

    public function testUnauthenticatedAjaxAccessReturns401Json(): void
    {
        $container = new AppContainer();
        $customer = new Customer();
        $container->bind('customer', $customer);

        $customerRepo = $this->createMock(CustomerRepository::class);
        $twig = new TwigEnvironment(new ArrayLoader([]));
        $translator = new Translator('pt-br');

        $action = new NewsletterAction($customerRepo, $container, $twig, $translator);

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/account/newsletter')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withAttribute('lang', 'pt-br');
        $response = new Response();

        $result = $action($request, $response, []);

        $this->assertSame(401, $result->getStatusCode());
        $this->assertStringContainsString('application/json', $result->getHeaderLine('Content-Type'));
        $data = json_decode((string)$result->getBody(), true);
        $this->assertFalse($data['success']);
    }

    public function testAuthenticatedGetRendersNewsletterPage(): void
    {
        $container = new AppContainer();
        $customer = new Customer();
        $customer->setUser((object)[
            'id' => 10,
            'name' => 'Teste Silva',
            'email' => 'teste@exemplo.com',
            'customer_group_id' => 1
        ]);
        $container->bind('customer', $customer);

        $customerEntity = new CustomerEntity();
        $customerEntity->setId(10);
        $customerEntity->setNewsletter(true);

        $customerRepo = $this->createMock(CustomerRepository::class);
        $customerRepo->method('find')->with(10)->willReturn($customerEntity);

        $twig = new TwigEnvironment(new ArrayLoader([
            'pages/users/accounts/newsletter.twig' => '<h1>{{ heading_title }}</h1><p>{{ newsletter_status ? "inscrito" : "nao_inscrito" }}</p>'
        ]));
        $translator = new Translator('pt-br');

        $routeParser = $this->createMock(RouteParserInterface::class);
        $routeParser->method('urlFor')->willReturnCallback(function (string $name, array $data = []) {
            return '/' . ($data['lang'] ?? 'pt-br') . '/' . $name;
        });

        $action = new NewsletterAction($customerRepo, $container, $twig, $translator);

        $request = $this->requestFactory->createServerRequest('GET', '/pt-br/account/newsletter')
            ->withAttribute('lang', 'pt-br')
            ->withAttribute(RouteContext::ROUTE_PARSER, $routeParser);
        $response = new Response();

        $result = $action($request, $response, []);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertStringContainsString('text/html', $result->getHeaderLine('Content-Type'));
        $body = (string)$result->getBody();
        $this->assertStringContainsString('inscrito', $body);
    }

    public function testAuthenticatedAjaxPostUpdatesNewsletterAndReturnsJson(): void
    {
        $container = new AppContainer();
        $customer = new Customer();
        $customer->setUser((object)[
            'id' => 10,
            'name' => 'Teste Silva',
            'email' => 'teste@exemplo.com',
            'customer_group_id' => 1
        ]);
        $container->bind('customer', $customer);

        $customerEntity = new CustomerEntity();
        $customerEntity->setId(10);
        $customerEntity->setNewsletter(false);

        $customerRepo = $this->createMock(CustomerRepository::class);
        $customerRepo->method('find')->with(10)->willReturn($customerEntity);
        $customerRepo->expects($this->once())
            ->method('updateProfile')
            ->with($this->callback(function (CustomerEntity $c) {
                return $c->isNewsletter() === true;
            }));

        $twig = new TwigEnvironment(new ArrayLoader([]));
        $translator = new Translator('pt-br');

        $action = new NewsletterAction($customerRepo, $container, $twig, $translator);

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/account/newsletter')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withParsedBody(['newsletter' => '1'])
            ->withAttribute('lang', 'pt-br');
        $response = new Response();

        $result = $action($request, $response, []);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertStringContainsString('application/json', $result->getHeaderLine('Content-Type'));
        $data = json_decode((string)$result->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testAuthenticatedFormPostUpdatesNewsletterAndRedirects(): void
    {
        $container = new AppContainer();
        $customer = new Customer();
        $customer->setUser((object)[
            'id' => 10,
            'name' => 'Teste Silva',
            'email' => 'teste@exemplo.com',
            'customer_group_id' => 1
        ]);
        $container->bind('customer', $customer);

        $customerEntity = new CustomerEntity();
        $customerEntity->setId(10);
        $customerEntity->setNewsletter(true);

        $customerRepo = $this->createMock(CustomerRepository::class);
        $customerRepo->method('find')->with(10)->willReturn($customerEntity);
        $customerRepo->expects($this->once())
            ->method('updateProfile')
            ->with($this->callback(function (CustomerEntity $c) {
                return $c->isNewsletter() === false;
            }));

        $twig = new TwigEnvironment(new ArrayLoader([]));
        $translator = new Translator('pt-br');

        $action = new NewsletterAction($customerRepo, $container, $twig, $translator);

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/account/newsletter')
            ->withParsedBody(['newsletter' => '0'])
            ->withAttribute('lang', 'pt-br');
        $response = new Response();

        $result = $action($request, $response, []);

        $this->assertSame(302, $result->getStatusCode());
        $this->assertSame('/pt-br/account', $result->getHeaderLine('Location'));
    }
}
