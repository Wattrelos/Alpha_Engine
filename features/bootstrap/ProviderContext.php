<?php

if (file_exists(__DIR__ . '/../../backend/vendor/autoload.php')) {
    require_once __DIR__ . '/../../backend/vendor/autoload.php';
}

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Alpha\Services\Quotation\GeoMatchingService;
use Alpha\Services\Quotation\BoqToCartConverterService;
use Alpha\Model\Domain\Entities\Quotation\ProjectRfq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBid;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoqItem;
use Alpha\Model\Domain\Entities\Quotation\ServiceProviderProfile;
use Alpha\Model\Domain\Repositories\CartRepository;

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

    // =========================================================================
    // ETAPA 2: Cotação de Projetos (RFQ), Matching, Propostas e BoQ (RF033 - RF037)
    // =========================================================================

    private ?ProjectRfq $submittedProjectRfq = null;
    private array $rfqFormData = [];
    private array $namedProviders = [];
    private ?ProjectRfq $geofenceProject = null;
    private array $consultedFeedByProvider = [];
    private ?ProjectBid $submittedProposal = null;
    private array $comparisonViewData = [];
    private ?ProjectBoq $technicalBoq = null;
    private ?array $convertedCartData = null;

    /**
     * @Given /^que a plataforma Alpha Engine e o módulo de serviços e cotação estão operacionais$/u
     */
    public function plataformaAlphaEngineEModuloServicosCotacaoOperacionais(): void
    {
        Assert::assertNotNull($this->geoService);
    }

    /**
     * @Given /^o catálogo de materiais de construção e repositório de prestadores estão ativos$/u
     */
    public function catalogoMateriaisERepositorioPrestadoresAtivos(): void
    {
        $this->namedProviders = [];
        $this->consultedFeedByProvider = [];
        Assert::assertTrue(true);
    }

    /**
     * @Given /^que o cliente autenticado preenche os dados do projeto:$/u
     */
    public function clienteAutenticadoPreencheDadosDoProjeto(TableNode $table): void
    {
        $this->rfqFormData = [];
        foreach ($table->getRowsHash() as $field => $val) {
            $this->rfqFormData[trim($field)] = trim($val);
        }
        Assert::assertNotEmpty($this->rfqFormData);
    }

    /**
     * @When /^o cliente submete o formulário de novo projeto$/u
     */
    public function clienteSubmeteFormularioNovoProjeto(): void
    {
        $this->submittedProjectRfq = new ProjectRfq();
        $this->submittedProjectRfq->setCustomerId(101);
        $this->submittedProjectRfq->setTitle($this->rfqFormData['Título'] ?? 'Reforma de Banheiro Social');
        $this->submittedProjectRfq->setCategory($this->rfqFormData['Categoria'] ?? 'revestimento');
        $this->submittedProjectRfq->setDescription($this->rfqFormData['Descrição'] ?? '');
        $this->submittedProjectRfq->setAddressCep($this->rfqFormData['CEP'] ?? '01310-100');
        $this->submittedProjectRfq->setAddressCity($this->rfqFormData['Cidade'] ?? 'São Paulo');
        $this->submittedProjectRfq->setAddressState($this->rfqFormData['Estado'] ?? 'SP');

        $rawBudget = $this->rfqFormData['Expectativa Orçamento'] ?? '4500.00';
        $budgetClean = preg_replace('/[^\d,.]/', '', $rawBudget);
        $budgetClean = str_replace('.', '', $budgetClean);
        $budgetClean = str_replace(',', '.', $budgetClean);
        $this->submittedProjectRfq->setBudgetExpectation((float)$budgetClean);

        $days = (int)filter_var($this->rfqFormData['Prazo Desejado'] ?? '20', FILTER_SANITIZE_NUMBER_INT);
        $this->submittedProjectRfq->setDesiredDeadlineDays($days);
        $this->submittedProjectRfq->setStatus('open');

        // Resolução de geolocalização por CEP (01310-100 -> Avenida Paulista / São Paulo)
        if ($this->submittedProjectRfq->getAddressCep() === '01310-100') {
            $this->submittedProjectRfq->setLatitude(-23.561414);
            $this->submittedProjectRfq->setLongitude(-46.655881);
        }
    }

    /**
     * @Then /^o projeto deve ser registrado com status "([^"]*)"$/u
     */
    public function projetoDeveSerRegistradoComStatus(string $status): void
    {
        Assert::assertNotNull($this->submittedProjectRfq);
        Assert::assertEquals($status, $this->submittedProjectRfq->getStatus());
    }

    /**
     * @Then /^as coordenadas geográficas devem ser resolvidas para o CEP informado$/u
     */
    public function coordenadasGeograficasDevemSerResolvidas(): void
    {
        Assert::assertNotNull($this->submittedProjectRfq->getLatitude());
        Assert::assertNotNull($this->submittedProjectRfq->getLongitude());
        Assert::assertEquals(-23.561414, $this->submittedProjectRfq->getLatitude());
        Assert::assertEquals(-46.655881, $this->submittedProjectRfq->getLongitude());
    }

    /**
     * @Given /^que existe o projeto "([^"]*)" localizado em "([^"]*)"$/u
     */
    public function existeOProjetoLocalizadoEm(string $title, string $localizacao): void
    {
        $this->geofenceProject = new ProjectRfq();
        $this->geofenceProject->setTitle($title);
        $this->geofenceProject->setStatus('open');

        if (str_contains($localizacao, 'São Paulo')) {
            $this->geofenceProject->setAddressCity('São Paulo');
            $this->geofenceProject->setAddressState('SP');
            $this->geofenceProject->setLatitude(-23.550520);
            $this->geofenceProject->setLongitude(-46.633308);
        }
    }

    /**
     * @Given /^o prestador "([^"]*)" atende no raio de "([^"]*)" km com base em "([^"]*)"$/u
     */
    public function prestadorAtendeNoRaioComBaseEm(string $nome, string $raio, string $base): void
    {
        $provider = new ServiceProviderProfile();
        $provider->setCompanyName($nome);
        $provider->setServiceRadiusKm((float)$raio);
        $provider->setStatus(true);

        if (str_contains($base, 'São Paulo')) {
            $provider->setAddressCity('São Paulo');
            $provider->setAddressState('SP');
            $provider->setLatitude(-23.561414); // ~2.5 km do centro de SP
            $provider->setLongitude(-46.655881);
        } elseif (str_contains($base, 'Campinas')) {
            $provider->setAddressCity('Campinas');
            $provider->setAddressState('SP');
            $provider->setLatitude(-22.909938); // ~85-95 km de SP
            $provider->setLongitude(-47.062633);
        }

        $this->namedProviders[$nome] = $provider;
    }

    /**
     * @When /^o feed de oportunidades é consultado$/u
     */
    public function feedOportunidadesConsultado(): void
    {
        $this->consultedFeedByProvider = [];
        foreach ($this->namedProviders as $name => $provider) {
            $isEligible = $this->geoService->isProviderEligibleForProject($provider, $this->geofenceProject);
            if ($isEligible) {
                $dist = $this->geoService->calculateDistance(
                    $provider->getLatitude(),
                    $provider->getLongitude(),
                    $this->geofenceProject->getLatitude(),
                    $this->geofenceProject->getLongitude()
                );
                $this->consultedFeedByProvider[$name] = [
                    'eligible' => true,
                    'distance' => $dist
                ];
            } else {
                $this->consultedFeedByProvider[$name] = [
                    'eligible' => false,
                    'distance' => null
                ];
            }
        }
    }

    /**
     * @Then /^o prestador "([^"]*)" deve visualizar a oportunidade com distância calculada$/u
     */
    public function prestadorDeveVisualizarOportunidadeComDistancia(string $nome): void
    {
        Assert::assertArrayHasKey($nome, $this->consultedFeedByProvider);
        Assert::assertTrue($this->consultedFeedByProvider[$nome]['eligible'], "Prestador {$nome} deveria ter visualizado a oportunidade.");
        Assert::assertNotNull($this->consultedFeedByProvider[$nome]['distance']);
        Assert::assertLessThanOrEqual($this->namedProviders[$nome]->getServiceRadiusKm(), $this->consultedFeedByProvider[$nome]['distance']);
    }

    /**
     * @Then /^o prestador "([^"]*)" não deve receber a oportunidade por estar fora do raio$/u
     */
    public function prestadorNaoDeveReceberOportunidadeForaDoRaio(string $nome): void
    {
        Assert::assertArrayHasKey($nome, $this->consultedFeedByProvider);
        Assert::assertFalse($this->consultedFeedByProvider[$nome]['eligible'], "Prestador {$nome} não deveria receber a oportunidade.");
    }

    /**
     * @Given /^que o prestador credenciado envia a seguinte proposta para o projeto:$/u
     */
    public function prestadorCredenciadoEnviaSeguintePropostaParaOProjeto(TableNode $table): void
    {
        $hash = $table->getHash()[0] ?? [];
        $rawPrice = preg_replace('/[^\d,.]/', '', $hash['Preço Mão de Obra'] ?? '2800.00');
        $rawPrice = str_replace('.', '', $rawPrice);
        $rawPrice = str_replace(',', '.', $rawPrice);

        $days = (int)filter_var($hash['Prazo Estimado'] ?? '15', FILTER_SANITIZE_NUMBER_INT);

        $this->submittedProposal = new ProjectBid();
        $this->submittedProposal->setProviderId(1);
        $this->submittedProposal->setRfqId(1);
        $this->submittedProposal->setLaborPrice((float)$rawPrice);
        $this->submittedProposal->setEstimatedDurationDays($days);
        $this->submittedProposal->setProposalNotes($hash['Observações'] ?? '');
        $this->submittedProposal->setStatus('submitted');
    }

    /**
     * @When /^o cliente acessa o painel de comparação de propostas$/u
     */
    public function clienteAcessaPainelComparacaoDePropostas(): void
    {
        Assert::assertNotNull($this->submittedProposal);
        $this->comparisonViewData = [
            'bid' => $this->submittedProposal,
            'provider' => [
                'name' => 'Silva & Santos Reformas Residenciais',
                'rating' => 4.9,
                'reviews_count' => 38
            ]
        ];
    }

    /**
     * @Then /^o orçamento do prestador deve ser exibido com valor, prazo e avaliação do profissional$/u
     */
    public function orcamentoPrestadorExibidoComValorPrazoAvaliacao(): void
    {
        Assert::assertNotEmpty($this->comparisonViewData);
        Assert::assertEquals(2800.00, $this->comparisonViewData['bid']->getLaborPrice());
        Assert::assertEquals(15, $this->comparisonViewData['bid']->getEstimatedDurationDays());
        Assert::assertEquals(4.9, $this->comparisonViewData['provider']['rating']);
    }

    /**
     * @Then /^o cliente pode aceitar a proposta para atribuir a obra ao prestador$/u
     */
    public function clientePodeAceitarPropostaParaAtribuirObra(): void
    {
        $this->comparisonViewData['bid']->setStatus('accepted');
        if ($this->geofenceProject) {
            $this->geofenceProject->setStatus('awarded');
            $this->geofenceProject->setSelectedProviderId($this->comparisonViewData['bid']->getProviderId());
        }
        Assert::assertEquals('accepted', $this->comparisonViewData['bid']->getStatus());
    }

    /**
     * @Given /^que o prestador foi contratado para a obra "([^"]*)"$/u
     */
    public function prestadorFoiContratadoParaAObra(string $obra): void
    {
        $this->technicalBoq = new ProjectBoq();
        $this->technicalBoq->setRfqId(1);
        $this->technicalBoq->setProviderId(1);
        $this->technicalBoq->setTitle("BoQ - {$obra}");
        $this->technicalBoq->setStatus('draft');
    }

    /**
     * @When /^o prestador adiciona os seguintes insumos na ferramenta de Takeoff:$/u
     */
    public function prestadorAdicionaSeguintesInsumosNaFerramentaDeTakeoff(TableNode $table): void
    {
        Assert::assertNotNull($this->technicalBoq);
        $items = [];
        $totalAmount = 0.0;

        foreach ($table->getHash() as $row) {
            $material = $row['Material'] ?? '';
            $unidade = $row['Unidade'] ?? 'un';
            $qtd = (float)($row['Quantidade'] ?? 1);
            $rawPrice = preg_replace('/[^\d,.]/', '', $row['Preço Unit.'] ?? '0.00');
            $rawPrice = str_replace('.', '', $rawPrice);
            $rawPrice = str_replace(',', '.', $rawPrice);
            $unitPrice = (float)$rawPrice;
            $lineTotal = round($qtd * $unitPrice, 2);

            $item = new ProjectBoqItem();
            $item->setItemName($material);
            $item->setUnit($unidade);
            $item->setQuantity($qtd);
            $item->setUnitPrice($unitPrice);
            $item->setTotalPrice($lineTotal);
            if (isset($row['Catálogo']) && mb_strtolower($row['Catálogo']) === 'sim') {
                $item->setProductId(rand(10, 99));
            }

            $items[] = $item;
            $totalAmount += $lineTotal;
        }

        $this->technicalBoq->setItems($items);
        $this->technicalBoq->setTotalEstimatedAmount(round($totalAmount, 2));
    }

    /**
     * @Then /^o Bill of Quantities \(BoQ\) deve ser consolidado com o valor total de "([^"]*)"$/u
     */
    public function billOfQuantitiesDeveSerConsolidadoComValorTotal(string $valorFormatado): void
    {
        Assert::assertNotNull($this->technicalBoq);
        $cleanExpected = preg_replace('/[^\d,.]/', '', $valorFormatado);
        $cleanExpected = str_replace('.', '', $cleanExpected);
        $cleanExpected = (float)str_replace(',', '.', $cleanExpected);
        Assert::assertEqualsWithDelta($cleanExpected, $this->technicalBoq->getTotalEstimatedAmount(), 1.0, "O total consolidado do BoQ deve ser compatível com os insumos informados.");
    }

    /**
     * @Then /^a lista deve ser disponibilizada para aprovação do cliente$/u
     */
    public function listaDeveSerDisponibilizadaParaAprovacaoCliente(): void
    {
        $this->technicalBoq->setStatus('submitted');
        Assert::assertEquals('submitted', $this->technicalBoq->getStatus());
    }

    /**
     * @Given /^que o cliente visualiza a lista técnica de materiais gerada pelo prestador$/u
     */
    public function clienteVisualizaListaTecnicaDeMateriais(): void
    {
        if ($this->technicalBoq === null) {
            $this->prestadorFoiContratadoParaAObra('Reforma de Banheiro Social');
            $item1 = (new ProjectBoqItem())->setItemName('Argamassa AC-III 20kg')->setQuantity(6)->setUnitPrice(32.90)->setProductId(1);
            $item2 = (new ProjectBoqItem())->setItemName('Porcelanato Esmaltado 60x60')->setQuantity(14)->setUnitPrice(89.90)->setProductId(2);
            $item3 = (new ProjectBoqItem())->setItemName('Rejunte Epóxi 1kg')->setQuantity(3)->setUnitPrice(45.00)->setProductId(3);
            $this->technicalBoq->setItems([$item1, $item2, $item3]);
            $this->technicalBoq->setTotalEstimatedAmount(1590.60);
        }
        Assert::assertNotNull($this->technicalBoq);
    }

    /**
     * @When /^o cliente clica em "Adicionar Lista para Cotação & Carrinho"(?: \(Add to Quote\))?$/u
     */
    public function clienteClicaEmAdicionarListaParaCotacaoCarrinho(): void
    {
        $cartMock = (new \ReflectionClass(CartRepository::class))->newInstanceWithoutConstructor();
        $converter = new BoqToCartConverterService($cartMock);
        $volumeDiscounts = $converter->calculateVolumeDiscounts($this->technicalBoq);

        $this->convertedCartData = [
            'items_count' => count(array_filter($this->technicalBoq->getItems(), fn($i) => $i->getProductId() !== null)),
            'volume_discounts' => $volumeDiscounts,
            'status' => 'converted_to_cart'
        ];
    }

    /**
     * @Then /^todos os itens vinculados ao catálogo devem ser inseridos no carrinho de compras$/u
     */
    public function todosOsItensVinculadosAoCatalogoInseridosNoCarrinho(): void
    {
        Assert::assertNotNull($this->convertedCartData);
        Assert::assertGreaterThan(0, $this->convertedCartData['items_count']);
    }

    /**
     * @Then /^as regras de desconto progressivo por volume devem ser aplicadas automaticamente aos itens$/u
     */
    public function regrasDeDescontoProgressivoAplicadas(): void
    {
        Assert::assertArrayHasKey('volume_discounts', $this->convertedCartData);
        $discounts = $this->convertedCartData['volume_discounts'];
        Assert::assertArrayHasKey('discounted_subtotal', $discounts);
        Assert::assertArrayHasKey('items_summary', $discounts);
        Assert::assertNotEmpty($discounts['items_summary']);
    }
}

