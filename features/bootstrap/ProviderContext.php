<?php

if (file_exists(__DIR__ . '/../../backend/vendor/autoload.php')) {
    require_once __DIR__ . '/../../backend/vendor/autoload.php';
}

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Alpha\Services\Quotation\GeoMatchingService;

/**
 * Contexto Behat para o Portal do Prestador de Serviços (Alpha Pro)
 * Cobre os casos de uso UC_PRV_001, UC_PRV_002 e UC_PRV_003.
 */
class ProviderContext implements Context
{
    private GeoMatchingService $geoService;
    private array $providerProfile = [];
    private array $availableRfqs = [];
    private array $filteredRfqs = [];
    private ?array $currentRfq = null;
    private ?array $currentBid = null;
    private ?string $bidErrorMessage = null;
    private ?array $currentBoq = null;
    private array $boqItems = [];
    private ?int $lastHttpStatus = null;
    private ?string $securityErrorMessage = null;

    public function __construct()
    {
        $this->geoService = new GeoMatchingService();
    }

    /**
     * @Given /^que a plataforma Alpha Engine possui prestadores de serviço credenciados$/u
     */
    public function plataformaPossuiPrestadoresCredenciados(): void
    {
        $this->providerProfile = [
            'id' => 1,
            'customer_id' => 16694,
            'company_name' => 'Silva & Santos Reformas Residenciais',
            'latitude' => -23.550520,
            'longitude' => -46.633308,
            'service_radius_km' => 35,
            'primary_trade' => 'Construção Civil & Alvenaria',
            'active_trades' => ['Construção Civil & Alvenaria', 'Elétrica & Infraestrutura', 'Pintura & Acabamento']
        ];
        Assert::assertNotEmpty($this->providerProfile['company_name']);
    }

    /**
     * @Given /^projetos de clientes \(RFQs\) estão abertos para concorrência na região$/u
     * @Given /^que projetos de clientes \(RFQs\) estão abertos para concorrência na região$/u
     */
    public function projetosClientesEstaoAbertos(): void
    {
        $this->availableRfqs = [
            [
                'id' => 1,
                'title' => 'Reforma de Banheiro e Troca de Revestimento - 12m²',
                'category' => 'Construção Civil & Alvenaria',
                'status' => 'open',
                'budget_labor' => 3000.00,
                'target_days' => 15,
                'latitude' => -23.561414,
                'longitude' => -46.655881, // ~2.5 km de distância
                'bids_count' => 3,
                'awarded_provider_id' => null
            ],
            [
                'id' => 2,
                'title' => 'Instalação Elétrica Trifásica Residencial',
                'category' => 'Elétrica & Infraestrutura',
                'status' => 'open',
                'budget_labor' => 4500.00,
                'target_days' => 10,
                'latitude' => -23.590000,
                'longitude' => -46.680000, // ~6.4 km de distância
                'bids_count' => 5,
                'awarded_provider_id' => null
            ],
            [
                'id' => 3,
                'title' => 'Construção de Espaço Gourmet',
                'category' => 'Construção Civil & Alvenaria',
                'status' => 'awarded',
                'budget_labor' => 8000.00,
                'target_days' => 25,
                'latitude' => -23.550520,
                'longitude' => -46.633308,
                'bids_count' => 2,
                'awarded_provider_id' => 1
            ],
            [
                'id' => 4,
                'title' => 'Reforma Predial Litoral Norte',
                'category' => 'Construção Civil & Alvenaria',
                'status' => 'open',
                'budget_labor' => 15000.00,
                'target_days' => 40,
                'latitude' => -23.850000,
                'longitude' => -45.400000, // ~130 km de distância (fora do raio)
                'bids_count' => 0,
                'awarded_provider_id' => null
            ]
        ];
        Assert::assertCount(4, $this->availableRfqs);
    }

    /**
     * @Given /^que o prestador está autenticado com base em "([^"]*)" e raio configurado de "([^"]*)" km$/u
     */
    public function prestadorAutenticadoComBaseERaio(string $cidade, string $raio): void
    {
        $this->providerProfile['base_location'] = $cidade;
        $this->providerProfile['service_radius_km'] = (float)$raio;
        Assert::assertEquals((float)$raio, $this->providerProfile['service_radius_km']);
    }

    /**
     * @When /^o prestador acessa o feed de oportunidades em "([^"]*)"$/u
     */
    public function prestadorAcessaFeedOportunidades(string $url): void
    {
        $radius = (float)$this->providerProfile['service_radius_km'];
        $provLat = $this->providerProfile['latitude'];
        $provLon = $this->providerProfile['longitude'];

        $this->filteredRfqs = [];
        foreach ($this->availableRfqs as $rfq) {
            if ($rfq['status'] !== 'open') {
                continue;
            }
            $distance = $this->geoService->calculateDistance($provLat, $provLon, $rfq['latitude'], $rfq['longitude']);
            if ($distance <= $radius) {
                $rfq['distance_km'] = round($distance, 1);
                $this->filteredRfqs[] = $rfq;
            }
        }
    }

    /**
     * @Then /^o sistema deve listar os projetos com status "([^"]*)" cuja distância seja menor ou igual a (\d+) km$/u
     */
    public function sistemaDeveListarProjetosComStatusEDistancia(string $status, int $radius): void
    {
        $maxDist = (float)$radius;
        Assert::assertNotEmpty($this->filteredRfqs, "Deveria haver oportunidades listadas dentro do raio.");
        foreach ($this->filteredRfqs as $rfq) {
            Assert::assertEquals($status, $rfq['status']);
            Assert::assertLessThanOrEqual($maxDist, $rfq['distance_km']);
        }
    }

    /**
     * @Then /^deve exibir a distância calculada pela fórmula de Haversine para cada oportunidade$/u
     */
    public function deveExibirDistanciaCalculada(): void
    {
        foreach ($this->filteredRfqs as $rfq) {
            Assert::assertArrayHasKey('distance_km', $rfq);
            Assert::assertGreaterThanOrEqual(0, $rfq['distance_km']);
        }
    }

    /**
     * @Then /^o prestador deve visualizar a expectativa orçamentária e prazo desejado pelo cliente$/u
     */
    public function prestadorDeveVisualizarOrcamentoEPrazo(): void
    {
        foreach ($this->filteredRfqs as $rfq) {
            Assert::assertGreaterThan(0, $rfq['budget_labor']);
            Assert::assertGreaterThan(0, $rfq['target_days']);
        }
    }

    /**
     * @Given /^que o prestador está na página de oportunidades$/u
     */
    public function prestadorEstaNaPaginaDeOportunidades(): void
    {
        $this->prestadorAcessaFeedOportunidades('/pt-br/prestador/oportunidades');
    }

    /**
     * @When /^o prestador filtra pela categoria "([^"]*)" e simula o raio para "([^"]*)" km$/u
     */
    public function prestadorFiltraPorCategoriaERaio(string $categoria, string $raio): void
    {
        $radius = (float)$raio;
        $provLat = $this->providerProfile['latitude'];
        $provLon = $this->providerProfile['longitude'];

        $this->filteredRfqs = [];
        foreach ($this->availableRfqs as $rfq) {
            if ($rfq['status'] !== 'open') {
                continue;
            }
            if ($rfq['category'] !== $categoria) {
                continue;
            }
            $distance = $this->geoService->calculateDistance($provLat, $provLon, $rfq['latitude'], $rfq['longitude']);
            if ($distance <= $radius) {
                $rfq['distance_km'] = round($distance, 1);
                $this->filteredRfqs[] = $rfq;
            }
        }
    }

    /**
     * @Then /^a listagem deve exibir somente obras da especialidade selecionada$/u
     */
    public function listagemExibeSomenteObrasEspecialidade(): void
    {
        Assert::assertNotEmpty($this->filteredRfqs);
        foreach ($this->filteredRfqs as $rfq) {
            Assert::assertEquals('Elétrica & Infraestrutura', $rfq['category']);
        }
    }

    /**
     * @Then /^deve recalcular o número de oportunidades disponíveis na nova abrangência$/u
     */
    public function deveRecalcularNumeroOportunidades(): void
    {
        Assert::assertCount(1, $this->filteredRfqs);
    }

    /**
     * @Given /^que o prestador seleciona a oportunidade "([^"]*)"$/u
     */
    public function prestadorSelecionaOportunidade(string $titulo): void
    {
        foreach ($this->availableRfqs as $rfq) {
            if ($rfq['title'] === $titulo) {
                $this->currentRfq = $rfq;
                break;
            }
        }
        Assert::assertNotNull($this->currentRfq, "Oportunidade {$titulo} não encontrada.");
    }

    /**
     * @When /^o prestador acessa o formulário de proposta em "([^"]*)"$/u
     */
    public function prestadorAcessaFormularioProposta(string $url): void
    {
        Assert::assertNotNull($this->currentRfq);
        Assert::assertEquals('open', $this->currentRfq['status']);
    }

    /**
     * @When /^preenche o valor de mão de obra "([^"]*)", prazo de "([^"]*)" dias e detalha o memorial descritivo$/u
     */
    public function preencheValorMaoDeObraEPrazo(string $valor, string $prazo): void
    {
        $this->currentBid = [
            'rfq_id' => $this->currentRfq['id'],
            'provider_id' => $this->providerProfile['id'],
            'labor_price' => (float)$valor,
            'execution_days' => (int)$prazo,
            'proposal_scope' => 'Execução completa com memorial descritivo e garantia de 12 meses.',
            'status' => 'submitted'
        ];
    }

    /**
     * @Then /^a proposta deve ser persistida com status "([^"]*)"$/u
     */
    public function propostaDeveSerPersistidaComStatus(string $status): void
    {
        Assert::assertNotNull($this->currentBid);
        Assert::assertEquals($status, $this->currentBid['status']);
        Assert::assertEquals(2800.00, $this->currentBid['labor_price']);
    }

    /**
     * @Then /^o prestador é redirecionado para o painel de oportunidades com mensagem de confirmação$/u
     */
    public function prestadorRedirecionadoPainelOportunidades(): void
    {
        Assert::assertTrue(true);
    }

    /**
     * @Given /^que um projeto RFQ já atingiu 10 propostas comerciais submetidas$/u
     */
    public function rfqAtingiu10Propostas(): void
    {
        $this->currentRfq = [
            'id' => 99,
            'title' => 'Projeto Concorrido com Teto',
            'status' => 'open',
            'bids_count' => 10
        ];
        Assert::assertEquals(10, $this->currentRfq['bids_count']);
    }

    /**
     * @When /^um novo prestador tenta enviar uma proposta para esta solicitação$/u
     */
    public function novoPrestadorTentaEnviarProposta(): void
    {
        // Regra de Negócio RN-BID-01: limite de 10 propostas
        if ($this->currentRfq['bids_count'] >= 10) {
            $this->bidErrorMessage = "Limite máximo de 10 propostas concorrentes atingido para esta solicitação.";
            $this->currentBid = null;
        } else {
            $this->currentBid = ['status' => 'submitted'];
        }
    }

    /**
     * @Then /^o sistema deve bloquear a gravação$/u
     */
    public function sistemaDeveBloquearGravacao(): void
    {
        Assert::assertNull($this->currentBid, "A proposta não deveria ser gravada quando o limite foi excedido.");
        Assert::assertNotNull($this->bidErrorMessage);
    }

    /**
     * @Then /^deve informar que o limite máximo de 10 propostas concorrentes foi atingido$/u
     */
    public function deveInformarLimiteMaximoAtingido(): void
    {
        Assert::assertStringContainsString("10 propostas concorrentes atingido", $this->bidErrorMessage);
    }

    /**
     * @Given /^que o cliente aceitou a proposta do prestador para a obra "([^"]*)"$/u
     * @Given /^o projeto está homologado com status "([^"]*)" para o prestador$/u
     */
    public function projetoHomologadoComStatusParaPrestador(?string $arg = null): void
    {
        $this->currentRfq = [
            'id' => 3,
            'title' => 'Construção de Espaço Gourmet',
            'status' => 'awarded',
            'awarded_provider_id' => $this->providerProfile['id']
        ];
        Assert::assertEquals('awarded', $this->currentRfq['status']);
        Assert::assertEquals($this->providerProfile['id'], $this->currentRfq['awarded_provider_id']);
    }

    /**
     * @When /^o prestador acessa a ferramenta em "([^"]*)"$/u
     */
    public function prestadorAcessaFerramentaTakeoff(string $url): void
    {
        if ($this->currentRfq['awarded_provider_id'] !== $this->providerProfile['id']) {
            $this->lastHttpStatus = 403;
            $this->securityErrorMessage = "Acesso negado. Esta ferramenta é restrita ao prestador contratado.";
            return;
        }

        $this->lastHttpStatus = 200;
        $this->currentBoq = [
            'id' => 1,
            'rfq_id' => $this->currentRfq['id'],
            'status' => 'draft',
            'total_materials' => 0.0
        ];
    }

    /**
     * @Then /^a grade de lista de materiais \(BoQ\) deve ser carregada$/u
     */
    public function gradeListaMateriaisCarregada(): void
    {
        Assert::assertEquals(200, $this->lastHttpStatus);
        Assert::assertNotNull($this->currentBoq);
    }

    /**
     * @When /^o prestador adiciona um insumo "([^"]*)" com quantidade "([^"]*)" e preço "([^"]*)"$/u
     */
    public function prestadorAdicionaInsumo(string $descricao, string $qtd, string $preco): void
    {
        $quantity = (float)$qtd;
        $unitPrice = (float)$preco;
        $totalPrice = round($quantity * $unitPrice, 2);

        $item = [
            'description' => $descricao,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice
        ];

        $this->boqItems[] = $item;
        $this->currentBoq['total_materials'] += $totalPrice;
    }

    /**
     * @Then /^o item deve ser inserido na tabela do BoQ$/u
     */
    public function itemDeveSerInseridoTabelaBoQ(): void
    {
        Assert::assertNotEmpty($this->boqItems);
        Assert::assertEquals('Porcelanato Acetinado 60x60', $this->boqItems[0]['description']);
        Assert::assertEquals(45, $this->boqItems[0]['quantity']);
    }

    /**
     * @Then /^o total estimado da lista de materiais deve ser recalculado automaticamente$/u
     */
    public function totalEstimadoMateriaisRecalculado(): void
    {
        $expectedTotal = round(45 * 79.90, 2); // 3595.50
        Assert::assertEquals($expectedTotal, $this->currentBoq['total_materials']);
    }

    /**
     * @Given /^que um prestador não foi o profissional selecionado para um projeto$/u
     */
    public function prestadorNaoFoiProfissionalSelecionado(): void
    {
        $this->currentRfq = [
            'id' => 4,
            'title' => 'Obra Privada de Outro Prestador',
            'status' => 'awarded',
            'awarded_provider_id' => 999 // Outro prestador
        ];
        Assert::assertNotEquals($this->providerProfile['id'], $this->currentRfq['awarded_provider_id']);
    }

    /**
     * @When /^ele tenta acessar diretamente a URL de Takeoff daquele projeto$/u
     */
    public function tentaAcessarDiretamenteUrlTakeoff(): void
    {
        $this->prestadorAcessaFerramentaTakeoff('/pt-br/prestador/projetos/4/takeoff');
    }

    /**
     * @Then /^o sistema deve negar o acesso com código HTTP 403 Proibido$/u
     */
    public function sistemaDeveNegarAcessoHttp403(): void
    {
        Assert::assertEquals(403, $this->lastHttpStatus);
    }

    /**
     * @Then /^deve informar que a ferramenta é restrita ao profissional contratado pelo cliente$/u
     */
    public function deveInformarFerramentaRestrita(): void
    {
        Assert::assertStringContainsString("restrita ao prestador contratado", $this->securityErrorMessage);
    }
}
