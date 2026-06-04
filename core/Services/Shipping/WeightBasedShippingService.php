<?php

namespace Alpha\Services\Shipping;

use Alpha\Model\Domain\Repositories\WeightClassRepository;
use Alpha\Model\Domain\Repositories\GeoZoneRepository;

/**
 * WeightBasedShippingService - Lógica de cálculo para frete baseado em peso.
 *
 * Processa faixas de preço no formato "Peso:Valor, Peso:Valor" associadas
 * a zonas geográficas, normalizando as unidades de medida via Alpha Engine.
 * Retorna dados brutos para a camada de apresentação (Action/Twig).
 */
class WeightBasedShippingService
{
    private WeightClassRepository $weightClassRepository;
    private GeoZoneRepository $geoZoneRepository;

    public function __construct()
    {
        $repositoryFactory = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance();
        $this->weightClassRepository = $repositoryFactory->get(WeightClassRepository::class);
        $this->geoZoneRepository     = $repositoryFactory->get(GeoZoneRepository::class);
    }

    /**
     * Verifica as faixas de peso por Geo Zone e retorna as opções brutas de frete.
     *
     * @param array  $address            Endereço de entrega (country_id, zone_id).
     * @param float  $totalWeight        Peso total bruto do carrinho.
     * @param int    $weightClassId      ID da classe de peso do carrinho.
     * @param int    $storeWeightClassId ID da classe de peso padrão da loja.
     * @param array  $zoneRates          Mapa ['geo_zone_id' => ['status' => bool, 'rate' => string, 'name' => string]].
     * @param int    $taxClassId         ID da classe de imposto do frete.
     * @param int    $sortOrder          Ordem de exibição.
     * @return array|null Cotações brutas por zona ou null se nenhuma zona aplicável.
     */
    public function getQuote(
        array $address,
        float $totalWeight,
        int   $weightClassId,
        int   $storeWeightClassId,
        array $zoneRates,
        int   $taxClassId = 0,
        int   $sortOrder = 0
    ): ?array {
        // Normaliza o peso do carrinho para a unidade padrão da loja
        $normalizedWeight = $this->weightClassRepository->convert($totalWeight, $weightClassId, $storeWeightClassId);

        $quote_data = [];

        foreach ($zoneRates as $geoZoneId => $zone) {
            if (empty($zone['status'])) {
                continue;
            }

            // O(1) graças ao Cache em Memória do GeoZoneRepository
            if (!$this->geoZoneRepository->isAddressInGeoZone((int)$geoZoneId, $address)) {
                continue;
            }

            $cost = null;

            // Extrai a configuração de faixas. Ex: "5:10.00, 7:12.00"
            foreach (explode(',', $zone['rate'] ?? '') as $rate) {
                $data = explode(':', $rate);
                if (isset($data[0], $data[1]) && (float)$data[0] >= $normalizedWeight) {
                    $cost = (float)$data[1];
                    break;
                }
            }

            if ($cost !== null) {
                $quote_data['weight_' . $geoZoneId] = [
                    'code'            => 'weight.weight_' . $geoZoneId,
                    'zone_name'       => $zone['name'] ?? '',
                    'normalized_weight' => $normalizedWeight,
                    'cost'            => $cost,
                    'tax_class_id'    => $taxClassId,
                ];
            }
        }

        if (empty($quote_data)) {
            return null;
        }

        return [
            'code'       => 'weight.weight',
            'quote'      => $quote_data,
            'sort_order' => $sortOrder,
        ];
    }
}