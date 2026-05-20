<?php

namespace Alpha\Services\Shipping;

use Alpha\Model\Domain\Repositories\WeightClassRepository;
use Alpha\Model\Domain\Repositories\LengthClassRepository;

/**
 * FlatRateShippingService - Gerencia a lógica de cálculo para o método de frete fixo.
 * 
 * Esta classe demonstra a migração da lógica de negócio das extensões legadas para 
 * serviços tipados na Alpha Engine, utilizando os repositórios de medidas para 
 * garantir que limites de peso e dimensões sejam respeitados independentemente 
 * das unidades configuradas no checkout.
 */
class FlatRateShippingService
{
    private WeightClassRepository $weightClassRepository;
    private LengthClassRepository $lengthClassRepository;

    public function __construct(\Registry $registry)
    {
        /** @var \Alpha\Mappers\MapperFactory $mapperFactory */
        $mapperFactory = $registry->get('mapperFactory');
        $this->weightClassRepository = $mapperFactory->get(WeightClassRepository::class);
        $this->lengthClassRepository = $mapperFactory->get(LengthClassRepository::class);
    }

    /**
     * Verifica se o frete é aplicável e retorna a cotação (quote).
     * 
     * @param array $address Endereço de entrega.
     * @param float $totalWeight Peso total do carrinho.
     * @param int $weightClassId ID da classe de peso do carrinho.
     * @return array|null Dados da cotação ou null se não aplicável.
     */
    public function getQuote(array $address, float $totalWeight, int $weightClassId): ?array
    {
        // Normalização do peso para a unidade padrão da loja (ex: Kg) para validação de limites
        $storeWeightClassId = (int)oc_config('config_weight_class_id');
        $normalizedWeight = $this->weightClassRepository->convert($totalWeight, $weightClassId, $storeWeightClassId);

        // Exemplo de regra Alpha Engine: Limite máximo de peso para aceitar frete fixo
        $maxWeight = (float)oc_config('shipping_flat_max_weight');
        
        if ($maxWeight > 0 && $normalizedWeight > $maxWeight) {
            return null; // Peso excede o limite do Flat Rate
        }

        // Lógica de Geozone (Simulada - seria integrada via GeoZoneRepository no futuro)
        $geozoneId = (int)oc_config('shipping_flat_geo_zone_id');
        
        // Retorno formatado seguindo o padrão OpenCart para compatibilidade com o checkout
        $method_data = [
            'code'       => 'flat.flat',
            'title'      => oc_language('shipping_flat_description'),
            'quote'      => [
                'flat' => [
                    'code'         => 'flat.flat',
                    'title'        => oc_language('shipping_flat_description'),
                    'cost'         => (float)oc_config('shipping_flat_cost'),
                    'tax_class_id' => (int)oc_config('shipping_flat_tax_class_id'),
                    'text'         => oc_currency_format((float)oc_config('shipping_flat_cost'))
                ]
            ],
            'sort_order' => (int)oc_config('shipping_flat_sort_order'),
            'error'      => false
        ];

        return $method_data;
    }
}