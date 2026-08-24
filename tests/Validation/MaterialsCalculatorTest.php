<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Alpha\Support\MaterialsCalculator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MaterialsCalculator::class)]
class MaterialsCalculatorTest extends TestCase
{
    /**
     * Valida o cenário de revestimento de parede exatamente como descrito no YAML:
     * - Comprimento: 4.0m, Largura: 3.0m, Pé-direito: 2.8m -> Área Bruta = 39.20 m²
     * - 2 Portas (0.8 x 2.1) = 3.36 m²
     * - 2 Janelas (1.2 x 1.0) = 2.40 m²
     * - Total Deduções = 5.76 m²
     * - Área Líquida = 33.44 m²
     * - Margem de Perda: 10% -> Total com Perda = 36.78 m² (36.784)
     * - Rendimento por caixa: 1.95 m²
     * - Total de Caixas = 19
     */
    public function testWallCoatingSimulationScenarioFromYaml(): void
    {
        $doors = [
            ['width' => 0.8, 'height' => 2.1, 'quantity' => 2],
        ];
        $windows = [
            ['width' => 1.2, 'height' => 1.0, 'quantity' => 2],
        ];

        $result = MaterialsCalculator::calculateWallCoating(
            length: 4.0,
            width: 3.0,
            height: 2.8,
            doors: $doors,
            windows: $windows,
            yieldPerBoxM2: 1.95,
            wasteMargin: 0.10
        );

        $this->assertEquals(39.20, $result['gross_wall_area_m2'], 'Área bruta de parede deve ser 39.20 m²');
        $this->assertEquals(3.36, $result['doors_area_m2'], 'Área das portas deve ser 3.36 m²');
        $this->assertEquals(2.40, $result['windows_area_m2'], 'Área das janelas deve ser 2.40 m²');
        $this->assertEquals(5.76, $result['total_deductions_m2'], 'Total de deduções deve ser 5.76 m²');
        $this->assertEquals(33.44, $result['net_wall_area_m2'], 'Área líquida de parede deve ser 33.44 m²');
        $this->assertEquals(10.0, $result['waste_margin_percent'], 'Margem de perda deve ser 10%');
        $this->assertEquals(36.78, $result['total_area_with_waste_m2'], 'Área total com perda deve ser 36.78 m²');
        $this->assertEquals(19, $result['calculated_boxes'], 'Total de caixas calculadas para parede deve ser 19');
    }

    /**
     * Valida o cenário de piso horizontal exatamente como descrito no YAML:
     * - Comprimento: 4.0m, Largura: 3.0m -> Área Bruta = 12.00 m²
     * - Margem de Perda: 10% -> Total com Perda = 13.20 m²
     * - Rendimento por caixa: 2.28 m²
     * - Total de Caixas = 6 (13.20 / 2.28 = 5.789 -> ceil = 6)
     */
    public function testFloorCoatingSimulationScenarioFromYaml(): void
    {
        $result = MaterialsCalculator::calculateFloorCoating(
            length: 4.0,
            width: 3.0,
            yieldPerBoxM2: 2.28,
            wasteMargin: 0.10
        );

        $this->assertEquals(12.00, $result['gross_floor_area_m2'], 'Área bruta de piso deve ser 12.00 m²');
        $this->assertEquals(10.0, $result['waste_margin_percent'], 'Margem de perda deve ser 10%');
        $this->assertEquals(13.20, $result['total_area_with_waste_m2'], 'Área total de piso com perda deve ser 13.20 m²');
        $this->assertEquals(6, $result['calculated_boxes'], 'Total de caixas calculadas para piso deve ser 6');
    }

    /**
     * Valida tratamento de casos de borda:
     * - Deduções maiores que a área bruta (área líquida não pode ser negativa)
     * - Rendimento mínimo para evitar divisão por zero
     */
    public function testEdgeCasesHandling(): void
    {
        // Caso em que vãos superam a área da parede
        $doors = [
            ['width' => 10.0, 'height' => 10.0, 'quantity' => 10],
        ];

        $result = MaterialsCalculator::calculateWallCoating(
            length: 2.0,
            width: 2.0,
            height: 2.0,
            doors: $doors,
            windows: [],
            yieldPerBoxM2: 2.0,
            wasteMargin: 0.10
        );

        $this->assertEquals(0.0, $result['net_wall_area_m2'], 'Área líquida não pode ser negativa.');
        $this->assertEquals(0.0, $result['total_area_with_waste_m2']);
        $this->assertEquals(0, $result['calculated_boxes']);

        // Caso com rendimento zero passado
        $floorResult = MaterialsCalculator::calculateFloorCoating(
            length: 0.0,
            width: 0.0,
            yieldPerBoxM2: 0.0
        );
        $this->assertEquals(0, $floorResult['calculated_boxes']);
    }
}
