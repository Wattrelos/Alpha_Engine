<?php

if (file_exists(__DIR__ . '/../../backend/vendor/autoload.php')) {
    require_once __DIR__ . '/../../backend/vendor/autoload.php';
}
if (file_exists(__DIR__ . '/../../backend/config.php')) {
    require_once __DIR__ . '/../../backend/config.php';
}

if (!defined('APPLICATION')) {
    define('APPLICATION', 'catalog');
}

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Auth\Services\CustomerAuthService;
use Alpha\Controller\Actions\Customer\Auth\LoginAction;
use Alpha\Controller\Actions\Customer\Auth\LogoutAction;
use Slim\Interfaces\RouteParserInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\DispatcherInterface;
use Slim\Routing\RoutingResults;
use Slim\Routing\RouteContext;

class AuthTestHelper extends TestCase
{
    public function makeStub(string $class)
    {
        return $this->createStub($class);
    }
}

/**
 * AuthContext - Contexto Behat para testes de autenticação e ciclo de vida de login/sessão integrados ao Banco de Dados (MySQL).
 */
class AuthContext implements Context
{
    private ServerRequestFactory $requestFactory;
    private ?Response $lastResponse = null;
    private ?array $lastJsonResponse = null;
    private ?CustomerRepository $customerRepository = null;
    private ?CustomerAuthService $authService = null;
    private ?string $activeSessionId = null;
    private ?string $intendedRedirect = null;
    private ?PDO $dbConnection = null;
    private array $createdEmails = [];
    private AuthTestHelper $helper;

    private static ?\Psr\Container\ContainerInterface $cachedContainer = null;

    public function __construct()
    {
        $this->requestFactory = new ServerRequestFactory();
        $this->helper = new AuthTestHelper('test');
        $this->initDatabaseEnvironment();
    }

    /**
     * Inicializa a conexão com o banco de dados MySQL e os repositórios reais da Alpha Engine.
     */
    private function initDatabaseEnvironment(): void
    {
        if (self::$cachedContainer === null) {
            $bootstrap = AppBootstrap::boot();
            self::$cachedContainer = $bootstrap->getContainer();
        }
        $repoFactory = self::$cachedContainer->get(RepositoryFactory::class);
        $this->customerRepository = $repoFactory->get(CustomerRepository::class);
        $this->authService = new CustomerAuthService($this->customerRepository);
        $this->dbConnection = ConnectionDB::getInstance()->getConnection();
    }

    /**
     * @BeforeScenario
     * @AfterScenario
     */
    public function cleanDatabaseTestData(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }

        if ($this->dbConnection !== null && !empty($this->createdEmails)) {
            $placeholders = implode(',', array_fill(0, count($this->createdEmails), '?'));
            $stmt = $this->dbConnection->prepare("DELETE FROM `" . DB_PREFIX . "customer` WHERE email IN ($placeholders)");
            $stmt->execute($this->createdEmails);

            $stmtLogin = $this->dbConnection->prepare("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE email IN ($placeholders)");
            $stmtLogin->execute($this->createdEmails);

            $this->createdEmails = [];
        }
    }

    private function createMockRequest(string $method, string $path, array $body = [], array $cookies = [])
    {
        $routeStub = $this->helper->makeStub(RouteInterface::class);
        $routeStub->method('getName')->willReturn('account.index');

        $routeParserStub = $this->helper->makeStub(RouteParserInterface::class);
        $routeParserStub->method('urlFor')->willReturnCallback(function (string $routeName, array $data = []) {
            if ($routeName === 'account.index') {
                return '/conta';
            }
            if ($routeName === 'login.form') {
                return '/login';
            }
            return '/' . $routeName;
        });

        $dispatcherStub = $this->helper->makeStub(DispatcherInterface::class);
        $routingResults = new RoutingResults($dispatcherStub, $method, $path, RoutingResults::FOUND);

        $request = $this->requestFactory->createServerRequest($method, $path)
            ->withParsedBody($body)
            ->withCookieParams($cookies)
            ->withAttribute(RouteContext::ROUTE, $routeStub)
            ->withAttribute(RouteContext::ROUTE_PARSER, $routeParserStub)
            ->withAttribute(RouteContext::ROUTING_RESULTS, $routingResults);

        return $request;
    }

    /**
     * @Given que o serviço de autenticação e repositório de clientes estão disponíveis
     */
    public function servicoDeAutenticacaoDisponivel()
    {
        Assert::assertNotNull($this->authService);
        Assert::assertNotNull($this->customerRepository);
        Assert::assertNotNull($this->dbConnection);
    }

    /**
     * Cadastra o cliente diretamente na base de dados MySQL com a senha hashada via password_hash().
     *
     * @Given que existe um cliente cadastrado com e-mail :email e senha :password
     */
    public function existeClienteCadastrado(string $email, string $password)
    {
        // Limpa registro prévio se existir
        $this->dbConnection->prepare("DELETE FROM `" . DB_PREFIX . "customer` WHERE email = ?")->execute([$email]);
        $this->dbConnection->prepare("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE email = ?")->execute([$email]);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->dbConnection->prepare("
            INSERT INTO `" . DB_PREFIX . "customer` 
                (store_id, customer_group_id, language_id, firstname, lastname, email, telephone, password, status, date_added, cpf_cnpj, persontype)
            VALUES 
                (1, 1, 2, 'Cliente', 'Teste', ?, '11987654321', ?, 1, NOW(), '12345678901', 'F')
        ");
        $stmt->execute([$email, $hashedPassword]);

        $this->createdEmails[] = $email;

        // Valida que o registro foi persistido fisicamente no BD
        $stmtCheck = $this->dbConnection->prepare("SELECT id FROM `" . DB_PREFIX . "customer` WHERE email = ? LIMIT 1");
        $stmtCheck->execute([$email]);
        Assert::assertNotEmpty($stmtCheck->fetchColumn(), "O cliente deve estar persistido no banco de dados MySQL.");
    }

    /**
     * Cadastra o cliente com status desativado (0) na base de dados MySQL.
     *
     * @Given que existe um cliente cadastrado com e-mail :email com o status inativo
     */
    public function existeClienteComStatusInativo(string $email)
    {
        $this->dbConnection->prepare("DELETE FROM `" . DB_PREFIX . "customer` WHERE email = ?")->execute([$email]);
        $this->dbConnection->prepare("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE email = ?")->execute([$email]);

        $hashedPassword = password_hash('Senha@123', PASSWORD_DEFAULT);

        $stmt = $this->dbConnection->prepare("
            INSERT INTO `" . DB_PREFIX . "customer` 
                (store_id, customer_group_id, language_id, firstname, lastname, email, telephone, password, status, date_added, cpf_cnpj, persontype)
            VALUES 
                (1, 1, 2, 'Cliente', 'Inativo', ?, '11988880000', ?, 0, NOW(), '12345678901', 'F')
        ");
        $stmt->execute([$email, $hashedPassword]);

        $this->createdEmails[] = $email;

        $stmtCheck = $this->dbConnection->prepare("SELECT status FROM `" . DB_PREFIX . "customer` WHERE email = ? LIMIT 1");
        $stmtCheck->execute([$email]);
        Assert::assertEquals(0, (int)$stmtCheck->fetchColumn(), "O cliente deve estar cadastrado como inativo no banco de dados MySQL.");
    }

    /**
     * @Given que o usuário tentou acessar a página restrita :url
     */
    public function usuarioTentouAcessarPaginaRestrita(string $url)
    {
        $this->intendedRedirect = $url;
    }

    /**
     * Submete a requisição de login que passa pela LoginAction e consulta a tabela real no BD.
     *
     * @When o usuário submete o formulário de login com e-mail :email e senha :password
     */
    public function usuarioSubmeteFormularioLogin(string $email, string $password)
    {
        $body = [
            'email'    => $email,
            'password' => $password,
        ];

        if ($this->intendedRedirect !== null) {
            $body['redirect'] = $this->intendedRedirect;
        }

        $request = $this->createMockRequest('POST', '/login', $body);
        $action = new LoginAction($this->authService);
        $response = new Response();

        $this->lastResponse = $action($request, $response, []);
        $bodyString = (string) $this->lastResponse->getBody();
        $this->lastJsonResponse = json_decode($bodyString, true);
    }

    /**
     * @When o usuário submete o formulário de login com credenciais válidas e parâmetro redirect :redirect
     */
    public function usuarioSubmeteFormularioComRedirect(string $redirect)
    {
        $this->intendedRedirect = $redirect;
        $this->existeClienteCadastrado('cliente.teste@alphaengine.com.br', 'SenhaSegura@123');
        $this->usuarioSubmeteFormularioLogin('cliente.teste@alphaengine.com.br', 'SenhaSegura@123');
    }

    /**
     * @Then o sistema deve autenticar o usuário com sucesso
     */
    public function sistemaDeveAutenticarComSucesso()
    {
        Assert::assertNotNull($this->lastResponse);
        Assert::assertEquals(200, $this->lastResponse->getStatusCode(), "Login válido com credenciais do banco deve retornar status 200.");
    }

    /**
     * @Then deve criar uma sessão autenticada para o cliente
     */
    public function deveCriarSessaoAutenticada()
    {
        Assert::assertNotNull($this->lastResponse);
        Assert::assertTrue($this->lastResponse->hasHeader('Set-Cookie'), "Deve emitir cabeçalho Set-Cookie com identificador de sessão.");
    }

    /**
     * @Then deve definir o cookie de sessão seguro :cookieName
     */
    public function deveDefinirCookieDeSessaoSeguro(string $cookieName)
    {
        Assert::assertNotNull($this->lastResponse);
        $cookieHeader = $this->lastResponse->getHeaderLine('Set-Cookie');
        Assert::assertStringContainsString($cookieName . '=', $cookieHeader);
        Assert::assertStringContainsString('HttpOnly', $cookieHeader);
    }

    /**
     * @Then a resposta deve indicar o redirecionamento para a página :url
     */
    public function respostaDeveIndicarRedirecionamento(string $url)
    {
        Assert::assertNotNull($this->lastJsonResponse);
        Assert::assertArrayHasKey('redirect', $this->lastJsonResponse);
        Assert::assertEquals($url, $this->lastJsonResponse['redirect']);
    }

    /**
     * @Then o sistema deve recusar a autenticação
     */
    public function sistemaDeveRecusarAutenticacao()
    {
        Assert::assertNotNull($this->lastResponse);
        Assert::assertGreaterThanOrEqual(400, $this->lastResponse->getStatusCode(), "Falha de autenticação no banco deve retornar status >= 400.");
    }

    /**
     * @Then deve retornar uma mensagem de erro :msg
     */
    public function deveRetornarMensagemDeErro(string $msg)
    {
        Assert::assertNotNull($this->lastJsonResponse);
        Assert::assertArrayHasKey('error', $this->lastJsonResponse);
        Assert::assertEquals($msg, $this->lastJsonResponse['error']['warning'] ?? '');
    }

    /**
     * @Then nenhuma sessão autenticada deve ser criada
     */
    public function nenhumaSessaoAutenticadaCriada()
    {
        Assert::assertNotNull($this->lastResponse);
        Assert::assertFalse($this->lastResponse->hasHeader('Set-Cookie'), "Não deve emitir cookie de autenticação em caso de erro.");
    }

    /**
     * @Then deve retornar o status de erro HTTP :status
     * @Then o status da resposta de autenticação deve ser :status
     */
    public function statusHttpDeveSer(int $status)
    {
        Assert::assertNotNull($this->lastResponse);
        Assert::assertEquals($status, $this->lastResponse->getStatusCode());
    }

    /**
     * @Then não deve permitir o acesso ao painel do cliente
     */
    public function naoDevePermitirAcessoAoPainel()
    {
        Assert::assertNotNull($this->lastResponse);
        Assert::assertNotEquals(200, $this->lastResponse->getStatusCode());
    }

    /**
     * @Given que o cliente :email possui uma sessão ativa
     */
    public function clientePossuiSessaoAtiva(string $email)
    {
        $this->activeSessionId = 'sess_teste_' . bin2hex(random_bytes(6));
    }

    /**
     * @When o cliente solicita o encerramento da sessão através da rota de logout
     */
    public function clienteSolicitaLogout()
    {
        $cookies = ['session_id' => $this->activeSessionId ?? 'sess_temp'];
        $request = $this->createMockRequest('GET', '/logout', [], $cookies);
        $action = new LogoutAction($this->authService);
        $response = new Response();

        $this->lastResponse = $action($request, $response, []);
    }

    /**
     * @Then o sistema deve invalidar a sessão do cliente
     */
    public function sistemaDeveInvalidarSessao()
    {
        Assert::assertNotNull($this->lastResponse);
        Assert::assertEquals(302, $this->lastResponse->getStatusCode(), "Logout deve redirecionar via status 302.");
        Assert::assertTrue($this->lastResponse->hasHeader('Location'), "Deve conter cabeçalho Location para redirecionamento.");
    }

    /**
     * @Then deve expirar o cookie de autenticação
     */
    public function deveExpirarCookieDeAutenticacao()
    {
        Assert::assertNotNull($this->lastResponse);
        $cookieHeader = $this->lastResponse->getHeaderLine('Set-Cookie');
        Assert::assertStringContainsString('session_id=', $cookieHeader);
        Assert::assertTrue(
            str_contains($cookieHeader, 'Max-Age=0') || str_contains($cookieHeader, 'expires=') || str_contains($cookieHeader, '1970'),
            "Cookie deve ter instrução de expiração no cabeçalho Set-Cookie."
        );
    }
}
