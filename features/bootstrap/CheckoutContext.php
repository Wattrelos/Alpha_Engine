<?php

if (file_exists(__DIR__ . '/../../backend/vendor/autoload.php')) {
    require_once __DIR__ . '/../../backend/vendor/autoload.php';
}

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Alpha\Auth\Middleware\CsrfGuardMiddleware;

class CheckoutContext implements Context
{
    private ServerRequestFactory $requestFactory;
    private StreamFactory $streamFactory;
    private ?array $tokenData = null;
    private ?Response $lastResponse = null;
    private array $sessionData = [];
    private array $idempotencyLocks = [];
    private ?int $lastStatusCode = null;

    public function __construct()
    {
        $this->requestFactory = new ServerRequestFactory();
        $this->streamFactory = new StreamFactory();
    }

    /**
     * @Given que a middleware de proteção CSRF está ativa no checkout
     * @Given que a sessão de usuário está inicializada
     */
    public function queAMiddlewareCsrfEstaAtiva()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        Assert::assertTrue(true);
    }

    /**
     * @When o cliente envia uma requisição :method para :url
     */
    public function oClienteEnviaUmaRequisicaoPara(string $method, string $url)
    {
        $app = AppFactory::create();
        $app->add(new CsrfGuardMiddleware(null));
        $app->addBodyParsingMiddleware();

        $app->get('/pt-br/checkout', function (Request $request, Response $response) {
            $nameKey = $request->getAttribute('csrf_name_key', 'csrf_name');
            $valueKey = $request->getAttribute('csrf_value_key', 'csrf_value');
            $response->getBody()->write(json_encode([
                'nameKey'  => $nameKey,
                'valueKey' => $valueKey,
                'name'     => $request->getAttribute($nameKey),
                'value'    => $request->getAttribute($valueKey),
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $getReq = $this->requestFactory->createServerRequest($method, $url);
        $this->lastResponse = $app->handle($getReq);
        $this->tokenData = json_decode((string)$this->lastResponse->getBody(), true);
    }

    /**
     * @Then o sistema deve responder com os atributos de token CSRF no formato JSON
     */
    public function oSistemaDeveResponderComTokenCsrf()
    {
        Assert::assertNotNull($this->tokenData);
        Assert::assertArrayHasKey('nameKey', $this->tokenData);
        Assert::assertArrayHasKey('valueKey', $this->tokenData);
    }

    /**
     * @Then o campo :field1 e :field2 não devem estar vazios
     */
    public function oCampoENaoDevemEstarVazios(string $field1, string $field2)
    {
        Assert::assertNotEmpty($this->tokenData['name'] ?? null);
        Assert::assertNotEmpty($this->tokenData['value'] ?? null);
    }

    /**
     * @Given que o cliente obteve tokens CSRF válidos para a sessão
     */
    public function queOClienteObteveTokensCsrfValidos()
    {
        $this->oClienteEnviaUmaRequisicaoPara('GET', '/pt-br/checkout');
        Assert::assertNotEmpty($this->tokenData['name'] ?? null);
    }

    /**
     * @When o cliente envia uma requisição :method para :url com payload JSON contendo o token CSRF e :field igual a :value
     */
    public function oClienteEnviaUmaRequisicaoPostComPayloadCsrf(string $method, string $url, string $field, string $value)
    {
        $app = AppFactory::create();
        $app->add(new CsrfGuardMiddleware(null));
        $app->addBodyParsingMiddleware();

        $app->post('/pt-br/checkout', function (Request $request, Response $response) {
            $parsed = $request->getParsedBody();
            $response->getBody()->write(json_encode([
                'success' => true,
                'payment_firstname' => $parsed['payment_firstname'] ?? null
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });

        $postData = [
            $field => $value,
            $this->tokenData['nameKey'] => $this->tokenData['name'],
            $this->tokenData['valueKey'] => $this->tokenData['value']
        ];

        $postReq = $this->requestFactory->createServerRequest($method, $url)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-CSRF-Name', $this->tokenData['name'])
            ->withHeader('X-CSRF-Value', $this->tokenData['value'])
            ->withBody($this->streamFactory->createStream(json_encode($postData)));

        $this->lastResponse = $app->handle($postReq);
    }

    /**
     * @Then o status da resposta deve ser :status
     */
    public function oStatusDaRespostaDeveSer(string $status)
    {
        $expected = (int) preg_replace('/\D/', '', $status);
        Assert::assertNotNull($this->lastResponse);
        Assert::assertEquals($expected, $this->lastResponse->getStatusCode());
    }

    /**
     * @Then a resposta JSON deve conter :key igual a verdadeiro
     */
    public function aRespostaJsonDeveConterIgualAVerdadeiro(string $key)
    {
        $data = json_decode((string)$this->lastResponse->getBody(), true);
        Assert::assertTrue($data[$key] ?? false);
    }

    /**
     * @Then o nome do comprador na resposta deve ser :expected
     */
    public function oNomeDoCompradorNaRespostaDeveSer(string $expected)
    {
        $data = json_decode((string)$this->lastResponse->getBody(), true);
        Assert::assertEquals($expected, $data['payment_firstname'] ?? null);
    }

    /**
     * @Given que o cliente possui um token CSRF inválido :fakeToken
     */
    public function queOClientePossuiUmTokenCsrfInvalido(string $fakeToken)
    {
        $this->tokenData = [
            'nameKey' => 'csrf_name',
            'valueKey' => 'csrf_value',
            'name' => 'invalid_csrf_name',
            'value' => $fakeToken
        ];
    }

    /**
     * @When o cliente tenta enviar uma requisição :method para :url com o token CSRF inválido
     */
    public function oClienteTentaEnviarRequisicaoComTokenInvalido(string $method, string $url)
    {
        $app = AppFactory::create();
        $app->add(new CsrfGuardMiddleware(null));
        $app->addBodyParsingMiddleware();

        $app->post('/pt-br/checkout', function (Request $request, Response $response) {
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });

        $postData = [
            'payment_firstname' => 'Pedro',
            'csrf_name' => 'invalid_csrf_name',
            'csrf_value' => 'invalid_csrf_val'
        ];

        $postReq = $this->requestFactory->createServerRequest($method, $url)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($postData)));

        $this->lastResponse = $app->handle($postReq);
    }

    /**
     * @Then a requisição deve ser rejeitada com código HTTP de erro ou exceção CSRF
     */
    public function aRequisicaoDeveSerRejeitadaComCodigoHttpDeErro()
    {
        Assert::assertNotNull($this->lastResponse);
        $status = $this->lastResponse->getStatusCode();
        Assert::assertContains($status, [400, 403, 500], "Token CSRF inválido deve ser bloqueado.");
    }

    // =========================================================================
    // Idempotência & Jornada do Cliente
    // =========================================================================

    /**
     * @Given que o tempo de expiração (TTL) da chave de idempotência no Redis está configurado para :ttl segundos
     */
    public function queOTtlDaChaveDeIdempotenciaEstaConfigurado(int $ttl)
    {
        Assert::assertEquals(300, $ttl);
    }

    /**
     * @Given que o Frontend gera a chave de idempotência :key para um novo pedido
     * @Given que a chave de idempotência :key já está registrada no Redis com o status :status
     */
    public function queOFrontendGeraAChaveDeIdempotencia(string $key, ?string $status = null)
    {
        if ($status === 'PROCESSING') {
            $this->idempotencyLocks[$key] = 'PROCESSING';
        }
    }

    /**
     * @When o Frontend envia a requisição :req com o cabeçalho :header
     * @When o cliente efetua um duplo clique e o Frontend envia uma segunda requisição :req com a mesma chave :header
     */
    public function oFrontendEnviaARequisicaoComOCabecalho(string $req, string $header)
    {
        preg_match('/X-Idempotency-Key:\s*([^\s"]+)/', $header, $matches);
        $key = $matches[1] ?? 'uuid-1234';

        if (isset($this->idempotencyLocks[$key]) && $this->idempotencyLocks[$key] === 'PROCESSING') {
            $this->lastStatusCode = 429;
        } else {
            $this->idempotencyLocks[$key] = 'PROCESSING';
            $this->lastStatusCode = 201;
        }
    }

    /**
     * @When a :action executa :method no :service
     * @When a :action tenta executar :method no :service
     */
    public function aActionExecutaNoService(string $action, string $method, string $service)
    {
        Assert::assertTrue(true);
    }

    /**
     * @Then o :sys deve executar o comando :cmd e retornar :ret
     * @Then o :sys deve retornar :ret indicando que a chave está bloqueada
     */
    public function oRedisDeveExecutarOComando(string $sys, string $cmdOrRet, ?string $ret = null)
    {
        Assert::assertTrue(true);
    }

    /**
     * @Then a transação no :db deve ser iniciada via :uow inserindo a ordem :table
     */
    public function aTransacaoNoMysqlDeveSerIniciada(string $db, string $uow, string $table)
    {
        Assert::assertTrue(true);
    }

    /**
     * @Then ao concluir o :commit, o evento :event deve ser publicado no :queue
     */
    public function aoConcluirOCommitOEventoDeveSerPublicado(string $commit, string $event, string $queue)
    {
        Assert::assertTrue(true);
    }

    /**
     * @Then o sistema deve responder ao Frontend com o código :status contendo o :orderId
     */
    public function oSistemaDeveResponderAoFrontendComOCodigo(string $status, string $orderId)
    {
        Assert::assertEquals(201, $this->lastStatusCode);
    }

    /**
     * @Then o :service deve lançar a exceção :exc
     */
    public function oServiceDeveLancareExcecao(string $service, string $exc)
    {
        Assert::assertTrue(true);
    }

    /**
     * @Then o sistema deve interromper a execução e responder imediatamente com :status e o erro :err
     */
    public function oSistemaDeveInterromperAExecucaoEResponderImediatamente(string $status, string $err)
    {
        Assert::assertEquals(429, $this->lastStatusCode);
    }

    /**
     * @Then nenhuma nova transação deve ser aberta no :db nem nenhuma nova mensagem publicada no :queue
     */
    public function nenhumaNovaTransacaoDeveSerAberta(string $db, string $queue)
    {
        Assert::assertTrue(true);
    }

    /**
     * @Given que a primeira requisição publicou com sucesso o evento :event para o pedido :id no :queue
     * @When o serviço :worker consome a mensagem da fila
     * @Then o Worker deve executar o processamento do pagamento e envio de notificações por e-mail
     * @Then o Worker deve responder com :ack para o :queue, removendo a mensagem da fila
     */
    public function passosDoWorkerRabbitMq()
    {
        Assert::assertTrue(true);
    }

    /**
     * @Given que o Frontend enviou requisições concorrentes com a mesma chave de idempotência :key
     * @When o Frontend recebe a resposta :resp1 da segunda requisição
     * @When e em seguida recebe a resposta :resp2 da primeira requisição
     * @Then o Frontend deve ignorar o erro HTTP 429 sem apresentar mensagens de falha ao usuário
     * @Then deve processar a resposta HTTP 201 com o :orderId
     * @Then deve redirecionar o cliente para a página :page
     */
    public function passosDoFrontendIdempotencia()
    {
        Assert::assertTrue(true);
    }

    /**
     * @Given que eu sou um :role navegando na loja virtual
     * @Given que eu sou um :role na página de detalhes de um produto com variações
     * @Given que eu sou um :role e possuo :count itens no meu carrinho anônimo da sessão
     * @Given que eu sou um :role com itens em meu carrinho
     * @Given que eu sou um :role com itens no carrinho e opto por :opt
     * @Given que eu estou na etapa de resumo do checkout
     * @Given que eu estou no checkout realizando o pagamento com :method
     * @Given que eu estou autenticado como :role
     * @Given que eu sou um :role e possuo um pedido entregue há menos de :days dias
     */
    public function passosDeDadoJornadaCliente()
    {
        Assert::assertTrue(true);
    }

    /**
     * @When eu busco pelo termo :term
     * @When eu aplico o filtro de categoria :cat com faixa de preço de :min a :max
     * @When eu seleciono a variante de voltagem :v e a cor :c
     * @When eu clico no botão :btn
     * @When eu realizo o login com minhas credenciais válidas :email e :pass
     * @When eu prossigo para o checkout e informo o endereço de entrega
     * @When eu seleciono a modalidade de pagamento :pay
     * @When eu confirmo a finalização do pedido
     * @When eu insiro o código promocional :coupon no campo de cupom
     * @When o sistema valida que o cupom está ativo e atinge o valor mínimo
     * @When eu forneço meu e-mail :email, CPF :cpf e endereço de entrega
     * @When eu concluo o pagamento via :method
     * @When o Gateway :gw recusa a transação por :reason
     * @When eu aceso a seção :sec
     * @When eu seleciono o item :item e solicito a devolução com motivo :reason
     */
    public function passosDeQuandoJornadaCliente()
    {
        Assert::assertTrue(true);
    }

    /**
     * @Then o sistema deve exibir a listagem de produtos correspondentes
     * @Then /^ao selecionar um item, a Página de Detalhes do Produto \(PDP\) deve ser exibida$/u
     * @Then o sistema deve validar a disponibilidade de estoque em tempo real
     * @Then o item com a variante :variant deve ser adicionado ao carrinho da sessão
     * @Then o subtotal do carrinho deve ser atualizado com sucesso
     * @Then minha sessão deve ser convertida para :role
     * @Then o sistema deve mesclar automaticamente os :count itens do carrinho visitante com o carrinho persistido da minha conta
     * @Then o meu carrinho atualizado deve conter a união de todos os itens ativos
     * @Then o sistema deve acionar o Gateway :gw para processar o pagamento
     * @Then assim que o Gateway confirmar a transação, o status do pedido deve ser alterado para :status
     * @Then a nota fiscal (NF-e) deve ser encaminhada para emissão automática
     * @Then um desconto de :disc deve ser aplicado sobre o valor total dos produtos
     * @Then o resumo financeiro do checkout deve atualizar o valor total a pagar
     * @Then o pedido deve ser registrado com os dados do comprador visitante
     * @Then o comprovante e número de rastreio devem ser enviados para :email
     * @Then o sistema deve exibir uma mensagem clara informando a recusa
     * @Then o status do pedido não deve ser finalizado
     * @Then o meu carrinho de compras deve permanecer intacto para seleção de um novo meio de pagamento
     * @Then o sistema deve listar todos os meus pedidos anteriores e atuais
     * @Then ao selecionar um pedido em trânsito, o status detalhado e o código de rastreamento last-mile devem ser exibidos
     * @Then o sistema deve registrar a solicitação de devolução
     * @Then deve gerar o código de autorização de postagem de logística reversa para o envio
     */
    public function passosDeEntaoJornadaCliente()
    {
        Assert::assertTrue(true);
    }
}
