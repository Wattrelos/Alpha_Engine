<?php

namespace Alpha\Services\Shipping;

use Alpha\Support\Registry;
use Alpha\Model\Domain\Repositories\WeightClassRepository;
use Alpha\Model\Domain\Repositories\LengthClassRepository;
use Alpha\Model\Domain\Repositories\GeoZoneRepository;
use Alpha\Mappers\MapperFactory;

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
    private Registry $registry;
    private WeightClassRepository $weightClassRepository;
    private LengthClassRepository $lengthClassRepository;
    private GeoZoneRepository $geoZoneRepository;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;

        /** @var MapperFactory $mapperFactory */
        $mapperFactory = $registry->get('mapperFactory');
        $this->weightClassRepository = $mapperFactory->get(WeightClassRepository::class);
        $this->lengthClassRepository = $mapperFactory->get(LengthClassRepository::class);
        $this->geoZoneRepository = $mapperFactory->get(GeoZoneRepository::class);
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
        $config = $this->registry->get('config');
        $language = $this->registry->get('language');
        $currency = $this->registry->get('currency');
        $session = $this->registry->get('session');
        $tax = $this->registry->get('tax');

        // Validação de Geo Zone via Alpha Engine
        $geoZoneId = (int)$config->get('shipping_flat_geo_zone_id');
        if ($geoZoneId > 0 && !$this->geoZoneRepository->isAddressInGeoZone($geoZoneId, $address)) {
            return null; // O endereço não atende a zona geográfica exigida
        }

        // Normalização do peso para a unidade padrão da loja (ex: Kg) para validação de limites
        $storeWeightClassId = (int)$config->get('config_weight_class_id');
        $normalizedWeight = $this->weightClassRepository->convert($totalWeight, $weightClassId, $storeWeightClassId);

        // Exemplo de regra Alpha Engine: Limite máximo de peso para aceitar frete fixo
        $maxWeight = (float)$config->get('shipping_flat_max_weight');
        
        if ($maxWeight > 0 && $normalizedWeight > $maxWeight) {
            return null; // Peso excede o limite do Flat Rate
        }

        $language->load('extension/opencart/shipping/flat');

        $cost = (float)$config->get('shipping_flat_cost');
        $taxClassId = (int)$config->get('shipping_flat_tax_class_id');
        
        // Retorno formatado seguindo o padrão OpenCart para compatibilidade com o checkout
        return [
            'code'       => 'flat.flat',
            'title'      => $language->get('text_title') ?: 'Frete Fixo',
            'quote'      => [
                'flat' => [
                    'code'         => 'flat.flat',
                    'title'        => $language->get('text_description') ?: 'Taxa Fixa de Frete',
                    'cost'         => $cost,
                    'tax_class_id' => $taxClassId,
                    'text'         => $currency->format($tax->calculate($cost, $taxClassId, $config->get('config_tax')), $session->data['currency'])
                ]
            ],
            'sort_order' => (int)$config->get('shipping_flat_sort_order'),
            'error'      => false
        ];
    }
}