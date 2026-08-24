<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Alpha\Services\Quotation\GeoMatchingService;
use Alpha\Services\Quotation\BoqToCartConverterService;
use Alpha\Services\Quotation\BoqSpreadsheetImportService;
use Alpha\Model\Domain\Entities\Quotation\ProjectRfq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBid;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoqItem;
use Alpha\Model\Domain\Entities\Quotation\ServiceProviderProfile;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\Repositories\ProjectBoqRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GeoMatchingService::class)]
#[CoversClass(BoqToCartConverterService::class)]
#[CoversClass(BoqSpreadsheetImportService::class)]
class QuotationAndTakeoffValidationTest extends TestCase
{
    private GeoMatchingService $geoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->geoService = new GeoMatchingService();
    }

    /**
     * RF034: Valida o cálculo de distância Haversine entre São Paulo e Campinas (~85-95 km).
     */
    public function testHaversineDistanceCalculation(): void
    {
        $distance = $this->geoService->calculateDistance(-23.5505, -46.6333, -22.9099, -47.0626);

        $this->assertGreaterThan(80.0, $distance);
        $this->assertLessThan(100.0, $distance);
    }

    /**
     * RF034: Valida matching de prestador dentro do raio de atendimento.
     */
    public function testProviderEligibleInsideRadius(): void
    {
        $provider = new ServiceProviderProfile();
        $provider->setStatus(true)
            ->setServiceRadiusKm(25.0)
            ->setLatitude(-23.5505)
            ->setLongitude(-46.6333);

        $project = new ProjectRfq();
        $project->setLatitude(-23.5600)
            ->setLongitude(-46.6500)
            ->setStatus('open');

        $isEligible = $this->geoService->isProviderEligibleForProject($provider, $project);
        $this->assertTrue($isEligible, 'Prestador deve ser considerado elegível para projeto a 2.5 km com raio de 25 km.');
    }

    /**
     * RF034: Valida rejeição de prestador fora do raio de atendimento.
     */
    public function testProviderRejectedOutsideRadius(): void
    {
        $provider = new ServiceProviderProfile();
        $provider->setStatus(true)
            ->setServiceRadiusKm(15.0)
            ->setLatitude(-23.5505)
            ->setLongitude(-46.6333);

        $project = new ProjectRfq();
        $project->setLatitude(-22.9099)
            ->setLongitude(-47.0626)
            ->setStatus('open');

        $isEligible = $this->geoService->isProviderEligibleForProject($provider, $project);
        $this->assertFalse($isEligible, 'Prestador não deve ser elegível para projeto fora do raio de cobertura.');
    }

    /**
     * RF034: Valida matching por fallback de cidade.
     */
    public function testProviderMatchingFallbackByCity(): void
    {
        $provider = new ServiceProviderProfile();
        $provider->setStatus(true)
            ->setAddressCity('São Paulo')
            ->setAddressState('SP');

        $project = new ProjectRfq();
        $project->setAddressCity('São Paulo')
            ->setAddressState('SP');

        $isEligible = $this->geoService->isProviderEligibleForProject($provider, $project);
        $this->assertTrue($isEligible, 'Fallback por cidade idêntica deve ser considerado elegível.');
    }

    /**
     * RF033: Valida instanciação da entidade ProjectRfq.
     */
    public function testProjectRfqEntityAttributes(): void
    {
        $rfq = new ProjectRfq();
        $rfq->setCustomerId(42)
            ->setTitle('Reforma de Banheiro')
            ->setCategory('revestimento')
            ->setDescription('Troca de pisos e azulejos')
            ->setAddressCep('01310-100')
            ->setAddressCity('São Paulo')
            ->setAddressState('SP')
            ->setBudgetExpectation(5000.00)
            ->setDesiredDeadlineDays(20)
            ->setStatus('open');

        $this->assertEquals(42, $rfq->getCustomerId());
        $this->assertEquals('Reforma de Banheiro', $rfq->getTitle());
        $this->assertEquals(5000.00, $rfq->getBudgetExpectation());
    }

    /**
     * RF035: Valida entidade ProjectBid.
     */
    public function testProjectBidEntityAttributes(): void
    {
        $bid = new ProjectBid();
        $bid->setRfqId(10)
            ->setProviderId(5)
            ->setLaborPrice(2800.50)
            ->setEstimatedDurationDays(12)
            ->setProposalNotes('Inclui remoção do entulho')
            ->setStatus('submitted');

        $this->assertEquals(10, $bid->getRfqId());
        $this->assertEquals(2800.50, $bid->getLaborPrice());
    }

    /**
     * RF036 / RF037 / RN015: Valida cálculo de desconto por volume progressivo no BoQ.
     */
    public function testVolumeDiscountCalculation(): void
    {
        $cartMock = $this->getMockBuilder(CartRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $converter = new BoqToCartConverterService($cartMock);

        $boq = new ProjectBoq();
        $boq->setId(1);

        // 12 unidades ao preço unitário de R$ 100,00 -> Tier 1 (10+) = 5% OFF -> R$ 95,00/un -> R$ 1.140,00 (Econ: R$ 60,00)
        $item1 = (new ProjectBoqItem())
            ->setId(1)
            ->setItemName('Porcelanato 60x60')
            ->setUnit('cx')
            ->setQuantity(12.0)
            ->setUnitPrice(100.00)
            ->setTotalPrice(1200.00);

        // 60 sacos ao preço unitário de R$ 30,00 -> Tier 2 (50+) = 10% OFF -> R$ 27,00/saco -> R$ 1.620,00 (Econ: R$ 180,00)
        $item2 = (new ProjectBoqItem())
            ->setId(2)
            ->setItemName('Cimento CP-II 50kg')
            ->setUnit('saco')
            ->setQuantity(60.0)
            ->setUnitPrice(30.00)
            ->setTotalPrice(1800.00);

        $boq->setItems([$item1, $item2]);

        $analysis = $converter->calculateVolumeDiscounts($boq);

        $this->assertEquals(3000.00, $analysis['original_subtotal'], 'Subtotal original deve ser R$ 3000,00');
        $this->assertEquals(2760.00, $analysis['discounted_subtotal'], 'Subtotal com desconto de volume deve ser R$ 2760,00');
        $this->assertEquals(240.00, $analysis['total_savings'], 'Economia total deve ser R$ 240,00');
        $this->assertEquals(8.0, $analysis['discount_percentage'], 'Percentual global de desconto deve ser 8%');
    }

    /**
     * RF036: Valida importação de planilha CSV com delimitadores e mapeamento inteligente de colunas.
     */
    public function testSpreadsheetImportService(): void
    {
        $boqRepoMock = $this->getMockBuilder(ProjectBoqRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['saveItem', 'recalculateTotal'])
            ->getMock();

        $boqRepoMock->expects($this->exactly(3))
            ->method('saveItem')
            ->willReturn(1);

        $boqRepoMock->expects($this->once())
            ->method('recalculateTotal')
            ->willReturn(1450.50);

        $importService = new BoqSpreadsheetImportService($boqRepoMock);

        // Cria arquivo CSV temporário para teste
        $tmpCsv = tempnam(sys_get_temp_dir(), 'test_boq_');
        $csvData = "Nome do Material;Unidade;Quantidade;Preço Unitário;Observações\n" .
                   "Argamassa AC-III 20kg;saco;15;32.50;Para piso do box\n" .
                   "Piso Porcelanato 80x80;cx;25;85.00;Sala e cozinha\n" .
                   "Rejunte Acrílico 1kg;un;4;40.00;Branco Neve\n";

        file_put_contents($tmpCsv, $csvData);

        $result = $importService->importCsv(10, $tmpCsv);
        unlink($tmpCsv);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['imported_count'], 'Três itens válidos devem ser importados da planilha.');
        $this->assertEquals(0, $result['error_count']);
        $this->assertEquals(1450.50, $result['total_value']);
    }

    /**
     * RF037 / RN015: Valida conversão do BoQ para carrinho com aplicação de descontos por volume.
     */
    public function testConvertBoqToCartWithVolumeDiscounts(): void
    {
        $cartMock = $this->getMockBuilder(CartRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['initializeContext', 'add'])
            ->getMock();

        $cartMock->expects($this->once())->method('initializeContext');
        $cartMock->expects($this->exactly(2))->method('add');

        $converter = new BoqToCartConverterService($cartMock);

        $boq = new ProjectBoq();
        $boq->setId(5);

        $item1 = (new ProjectBoqItem())
            ->setProductId(201)
            ->setItemName('Tijolo Cerâmico 8 Furos')
            ->setQuantity(500.0)
            ->setUnitPrice(1.20);

        $item2 = (new ProjectBoqItem())
            ->setProductId(202)
            ->setItemName('Areia Média Lavada m³')
            ->setQuantity(15.0)
            ->setUnitPrice(95.00);

        $boq->setItems([$item1, $item2]);

        $result = $converter->convertBoqToCart($boq);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['items_added']);
        $this->assertArrayHasKey('volume_discounts', $result);
        $this->assertGreaterThan(0, $result['volume_discounts']['total_savings']);
    }

    /**
     * RF036 / RF037: Valida inserção direta de item de produto no BoQ do projeto (Add to Quote).
     */
    public function testAddProductDirectlyToProjectBoq(): void
    {
        $boq = new ProjectBoq();
        $boq->setId(8)->setRfqId(12)->setTitle('Obra Teste');

        $item = new ProjectBoqItem();
        $item->setBoqId(8)
            ->setProductId(105)
            ->setItemName('Torneira Monocomando Gourmet')
            ->setUnit('un')
            ->setQuantity(2.0)
            ->setUnitPrice(249.90)
            ->setTotalPrice(499.80)
            ->setNotes('Adicionado diretamente da vitrine');

        $this->assertEquals(8, $item->getBoqId());
        $this->assertEquals(105, $item->getProductId());
        $this->assertEquals(499.80, $item->getTotalPrice());
        $this->assertEquals('un', $item->getUnit());
    }
}
