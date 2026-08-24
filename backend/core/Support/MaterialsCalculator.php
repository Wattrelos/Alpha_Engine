<?php

declare(strict_types=1);

namespace Alpha\Support;

/**
 * Calculadora de Materiais de Construção (Pisos e Revestimentos)
 * Baseada nos requisitos funcionais de construction_materials_calculator.yaml
 */
class MaterialsCalculator
{
    public const DEFAULT_WASTE_MARGIN = 0.10; // 10%

    /**
     * Calcula o revestimento de parede (m² bruto, deduções, líquido, total com perda e caixas).
     *
     * @param float $length Comprimento do cômodo em metros
     * @param float $width Largura do cômodo em metros
     * @param float $height Pé-direito / Altura das paredes em metros
     * @param array<array{width: float, height: float, quantity?: int}> $doors Lista de portas
     * @param array<array{width: float, height: float, quantity?: int}> $windows Lista de janelas
     * @param float $yieldPerBoxM2 Rendimento de cada caixa em m²
     * @param float $wasteMargin Percentual de perda (ex: 0.10 para 10%)
     * @return array{
     *   gross_wall_area_m2: float,
     *   doors_area_m2: float,
     *   windows_area_m2: float,
     *   total_deductions_m2: float,
     *   net_wall_area_m2: float,
     *   waste_margin_percent: float,
     *   total_area_with_waste_m2: float,
     *   yield_per_box_m2: float,
     *   calculated_boxes: int
     * }
     */
    public static function calculateWallCoating(
        float $length,
        float $width,
        float $height,
        array $doors = [],
        array $windows = [],
        float $yieldPerBoxM2 = 1.0,
        float $wasteMargin = self::DEFAULT_WASTE_MARGIN
    ): array {
        $length = max(0.0, $length);
        $width = max(0.0, $width);
        $height = max(0.0, $height);
        $wasteMargin = max(0.0, $wasteMargin);
        $yieldPerBoxM2 = max(0.0001, $yieldPerBoxM2);

        $grossWallArea = (2 * $length * $height) + (2 * $width * $height);

        $doorsArea = 0.0;
        foreach ($doors as $door) {
            $w = max(0.0, (float)$door['width']);
            $h = max(0.0, (float)$door['height']);
            $q = max(0, (int)($door['quantity'] ?? 1));
            $doorsArea += ($w * $h * $q);
        }

        $windowsArea = 0.0;
        foreach ($windows as $window) {
            $w = max(0.0, (float)$window['width']);
            $h = max(0.0, (float)$window['height']);
            $q = max(0, (int)($window['quantity'] ?? 1));
            $windowsArea += ($w * $h * $q);
        }

        $totalDeductions = $doorsArea + $windowsArea;
        $netWallArea = max(0.0, $grossWallArea - $totalDeductions);
        $totalAreaWithWaste = $netWallArea * (1 + $wasteMargin);
        $calculatedBoxes = (int) ceil(round($totalAreaWithWaste, 6) / $yieldPerBoxM2);

        return [
            'gross_wall_area_m2'       => round($grossWallArea, 2),
            'doors_area_m2'            => round($doorsArea, 2),
            'windows_area_m2'          => round($windowsArea, 2),
            'total_deductions_m2'      => round($totalDeductions, 2),
            'net_wall_area_m2'         => round($netWallArea, 2),
            'waste_margin_percent'     => round($wasteMargin * 100, 2),
            'total_area_with_waste_m2' => round($totalAreaWithWaste, 2),
            'yield_per_box_m2'         => round($yieldPerBoxM2, 4),
            'calculated_boxes'         => $calculatedBoxes,
        ];
    }

    /**
     * Calcula o revestimento de piso / pavimento (m² bruto, total com perda e caixas).
     *
     * @param float $length Comprimento do cômodo em metros
     * @param float $width Largura do cômodo em metros
     * @param float $yieldPerBoxM2 Rendimento de cada caixa em m²
     * @param float $wasteMargin Percentual de perda (ex: 0.10 para 10%)
     * @return array{
     *   gross_floor_area_m2: float,
     *   waste_margin_percent: float,
     *   total_area_with_waste_m2: float,
     *   yield_per_box_m2: float,
     *   calculated_boxes: int
     * }
     */
    public static function calculateFloorCoating(
        float $length,
        float $width,
        float $yieldPerBoxM2 = 1.0,
        float $wasteMargin = self::DEFAULT_WASTE_MARGIN
    ): array {
        $length = max(0.0, $length);
        $width = max(0.0, $width);
        $wasteMargin = max(0.0, $wasteMargin);
        $yieldPerBoxM2 = max(0.0001, $yieldPerBoxM2);

        $grossFloorArea = $length * $width;
        $totalAreaWithWaste = $grossFloorArea * (1 + $wasteMargin);
        $calculatedBoxes = (int) ceil(round($totalAreaWithWaste, 6) / $yieldPerBoxM2);

        return [
            'gross_floor_area_m2'      => round($grossFloorArea, 2),
            'waste_margin_percent'     => round($wasteMargin * 100, 2),
            'total_area_with_waste_m2' => round($totalAreaWithWaste, 2),
            'yield_per_box_m2'         => round($yieldPerBoxM2, 4),
            'calculated_boxes'         => $calculatedBoxes,
        ];
    }
}
