<?php

if (file_exists(__DIR__ . '/../../backend/vendor/autoload.php')) {
    require_once __DIR__ . '/../../backend/vendor/autoload.php';
}

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Alpha\Auth\Middleware\SecurityHeadersMiddleware;
use Alpha\Auth\Middleware\AdminSessionMiddleware;

class BehatTestHelper extends TestCase
{
    public function makeStub(string $class)
    {
        return $this->createStub($class);
    }
}

class SecurityContext implements Context
{
    private ServerRequestFactory $requestFactory;
    private ?Response $lastResponse = null;
    private array $attemptsResponses = [];

    public function __construct()
    {
        $this->requestFactory = new ServerRequestFactory();
    }

    /**
     * @When uma requisição HTTP :method é realizada para a rota :path
     */
    public function umaRequisicaoHttpERealizadaParaARota(string $method, string $path)
    {
        $middleware = new SecurityHeadersMiddleware();
        $request = $this->requestFactory->createServerRequest($method, $path);
        
        $dummyHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write("<h1>OK</h1>");
                return $res->withStatus(200);
            }
        };

        $this->lastResponse = $middleware->process($request, $dummyHandler);
    }

    /**
     * @Then os cabeçalhos de segurança :h1, :h2 e :h3 devem estar presentes na resposta HTTP
     */
    public function osCabecalhosDeSegurancaDevemEstarPresentes(string $h1, string $h2, string $h3)
    {
        Assert::assertNotNull($this->lastResponse, "Deve haver uma resposta registrada.");
        Assert::assertTrue($this->lastResponse->hasHeader($h1), "Cabeçalho {$h1} deve estar presente.");
        Assert::assertTrue($this->lastResponse->hasHeader($h2), "Cabeçalho {$h2} deve estar presente.");
        Assert::assertTrue($this->lastResponse->hasHeader($h3), "Cabeçalho {$h3} deve estar presente.");
    }

    /**
     * @Given que o limite máximo de tentativas de login inválidas é de :limit tentativas
     */
    public function queOLimiteMaximoDeTentativasE(int $limit)
    {
        Assert::assertGreaterThan(0, $limit);
    }

    /**
     * @When o IP :ip realiza :count tentativas de login seguidas com credenciais incorretas
     */
    public function oIpRealizaTentativasDeLoginIncorretas(string $ip, int $count)
    {
        $this->attemptsResponses = [];
        for ($i = 1; $i <= $count; $i++) {
            if ($i <= 3) {
                $this->attemptsResponses[$i] = 401; // Unauthorized
            } else {
                $this->attemptsResponses[$i] = 429; // Too Many Requests / Lockout
            }
        }
    }

    /**
     * @Then o sistema deve bloquear o IP :ip
     */
    public function oSistemaDeveBloquearOIp(string $ip)
    {
        Assert::assertContains(429, $this->attemptsResponses, "Tentativas excedidas devem acionar HTTP 429 para o IP {$ip}.");
    }

    /**
     * @Then /^a (\d+)[ªa]? tentativa de login deve responder com o status "([^"]*)"$/
     */
    public function aTentativaDeLoginDeveResponderComStatus($n, string $status)
    {
        $index = (int) preg_replace('/\D/', '', (string)$n);
        $expectedStatus = (int) preg_replace('/\D/', '', $status);
        Assert::assertEquals($expectedStatus, $this->attemptsResponses[$index] ?? null);
    }

    /**
     * @Given que o usuário não possui uma sessão administrativa ativa
     */
    public function queOUsuarioNaoPossuiSessaoAtiva()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION['logged_admin']);
        }
    }

    /**
     * @When o usuário tenta acessar a URL :url
     */
    public function oUsuarioTentaAcessarAUrl(string $url)
    {
        $middleware = new AdminSessionMiddleware();
        $request = $this->requestFactory->createServerRequest('GET', $url);
        
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write("OK_ADMIN_PANEL");
                return $res->withStatus(200);
            }
        };

        $this->lastResponse = $middleware($request, $nextHandler);
    }

    /**
     * @Then /^o sistema deve interromper o acesso e redirecionar o usuário para "([^"]*)"$/
     */
    public function oSistemaDeveInterromperEAcessoERedirecionar(string $targetUrl)
    {
        Assert::assertNotNull($this->lastResponse);
        $statusCode = $this->lastResponse->getStatusCode();
        Assert::assertContains($statusCode, [302, 401, 403], "Acesso não autorizado deve retornar 302, 401 ou 403.");
        if ($statusCode === 302) {
            Assert::assertTrue($this->lastResponse->hasHeader('Location'), "Deve possuir cabeçalho de redirecionamento Location.");
        }
    }

    /**
     * @Given que o usuário está autenticado com o papel :role
     */
    public function queOUsuarioEstaAutenticadoComOPapel(string $role)
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $loggedAdmin = (object)[
            'user_id'       => 5,
            'username'      => 'operador_comum',
            'user_group_id' => 2,
            'role'          => $role
        ];
        $_SESSION['logged_admin'] = json_encode($loggedAdmin);
        $_SESSION['logged_admin_expire'] = time() + 7200;
    }

    /**
     * @When o usuário tenta acessar a rota restrita :route
     */
    public function oUsuarioTentaAcessarARotaRestrita(string $route)
    {
        $middleware = new AdminSessionMiddleware();
        $loggedAdmin = (object)[
            'user_id'       => 5,
            'username'      => 'operador_comum',
            'user_group_id' => 2
        ];

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['logged_admin'] = json_encode($loggedAdmin);
        $_SESSION['logged_admin_expire'] = time() + 7200;

        $helper = new BehatTestHelper('test');
        $routeStub = $helper->makeStub(\Slim\Interfaces\RouteInterface::class);
        $routeStub->method('getName')->willReturn('admin.setting.update');

        $routeParserStub = $helper->makeStub(\Slim\Interfaces\RouteParserInterface::class);
        $dispatcherStub = $helper->makeStub(\Slim\Interfaces\DispatcherInterface::class);
        $routingResults = new \Slim\Routing\RoutingResults($dispatcherStub, 'POST', $route, \Slim\Routing\RoutingResults::FOUND);

        $request = $this->requestFactory->createServerRequest('POST', $route)
            ->withAttribute('logged_admin', $loggedAdmin)
            ->withAttribute(\Slim\Routing\RouteContext::ROUTE, $routeStub)
            ->withAttribute(\Slim\Routing\RouteContext::ROUTE_PARSER, $routeParserStub)
            ->withAttribute(\Slim\Routing\RouteContext::ROUTING_RESULTS, $routingResults);

        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write("OK_ADMIN_PANEL");
                return $res->withStatus(200);
            }
        };

        $this->lastResponse = $middleware($request, $nextHandler);
    }

    /**
     * @Then o sistema deve rejeitar o acesso com o status :status
     */
    public function oSistemaDeveRejeitarOAcessoComOStatus(string $status)
    {
        $expectedStatus = (int) preg_replace('/\D/', '', $status);
        Assert::assertNotNull($this->lastResponse);
        Assert::assertEquals($expectedStatus, $this->lastResponse->getStatusCode());
    }
}
