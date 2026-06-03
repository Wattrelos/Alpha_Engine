<?php

namespace Alpha\Services\Shipping;

use Psr\Container\ContainerInterface;
use Alpha\Model\Domain\Repositories\WeightClassRepository;
use Alpha\Model\Domain\Repositories\GeoZoneRepository;

/**
 * WeightBasedShippingService - Gerencia a lógica de cálculo para frete baseado em peso.
 * 
 * Processa faixas de preço no formato "Peso:Valor, Peso:Valor" associadas
 * a zonas geográficas, normalizando as unidades de medida via Alpha Engine.
 */
class WeightBasedShippingService
{
    private ContainerInterface $container;
    private WeightClassRepository $weightClassRepository;
    private GeoZoneRepository $geoZoneRepository;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        
        $repositoryFactory = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance();
        $this->weightClassRepository = $repositoryFactory->get(WeightClassRepository::class);
        $this->geoZoneRepository = $repositoryFactory->get(GeoZoneRepository::class);
    }

    /**
     * Verifica as faixas de peso por Geo Zone e retorna as opções de frete.
     * 
     * @param array $address Endereço de entrega (contendo country_id e zone_id).
     * @param float $totalWeight Peso total bruto do carrinho.
     * @param int $weightClassId ID da classe de peso originada no carrinho.
     * @return array|null Dados da cotação com as taxas por região, ou null se indisponível.
     */
    public function getQuote(array $address, float $totalWeight, int $weightClassId): ?array
    {
        $config = $this->container->get('config');
        $language = $this->container->get('language');
        $currency = $this->container->get('currency');
        $session = $this->container->get('session');

        $language->load('extension/opencart/shipping/weight');

        // Normaliza o peso do carrinho para a unidade padrão da loja (Ex: Converte gramas para Kg)
        $storeWeightClassId = (int)$config->get('config_weight_class_id');
        $normalizedWeight = $this->weightClassRepository->convert($totalWeight, $weightClassId, $storeWeightClassId);

        $quote_data = [];

        // Alpha Engine: Consulta as Zonas Geográficas via Repository
        $geoZones = $this->geoZoneRepository->getGeoZones();

        foreach ($geoZones as $result) {
            // Clean Code: Ignora rapidamente se a zona não estiver com frete habilitado
            if (!$config->get('shipping_weight_' . $result['geo_zone_id'] . '_status')) {
                continue;
            }
            
            // Clean Code: O(1) graças ao Cache em Memória que recém implementamos no GeoZoneRepository
            if (!$this->geoZoneRepository->isAddressInGeoZone((int)$result['geo_zone_id'], $address)) {
                continue;
            }

            $cost = '';
            
            // Extrai a configuração de faixas. Ex: "5:10.00, 7:12.00"
            $rates = explode(',', $config->get('shipping_weight_' . $result['geo_zone_id'] . '_rate'));

            foreach ($rates as $rate) {
                $data = explode(':', $rate);

                // Compara o peso normalizado do carrinho com o limite superior da faixa atual
                if (isset($data[0]) && $data[0] >= $normalizedWeight) {
                    if (isset($data[1])) {
                        $cost = $data[1];
                    }
                    break; // Interrompe após encontrar a faixa correta
                }
            }

            // Se um custo foi estabelecido, adiciona esta zona geográfica como opção de frete
            if ((string)$cost != '') {
                $weightText = $this->container->get('weight')->format($normalizedWeight, $storeWeightClassId);
                
                $quote_data['weight_' . $result['geo_zone_id']] = [
                    'code'         => 'weight.weight_' . $result['geo_zone_id'],
                    'title'        => $result['name'] . '  (' . $language->get('text_weight') . ' ' . $weightText . ')',
                    'cost'         => $cost,
                    'tax_class_id' => $config->get('shipping_weight_tax_class_id'),
                    'text'         => $currency->format($this->container->get('tax')->calculate($cost, $config->get('shipping_weight_tax_class_id'), $config->get('config_tax')), $session->data['currency'])
                ];
            }
        }

        // Se nenhuma Geo Zone bateu com o endereço ou configurou preço, o serviço é anulado
        if (empty($quote_data)) {
            return null;
        }

        $methodTitle = $language->get('text_title') !== 'text_title' ? $language->get('text_title') : 'Frete por Peso';

        return [
            'code'       => 'weight.weight',
            'title'      => $methodTitle,
            'quote'      => $quote_data,
            'sort_order' => (int)$config->get('shipping_weight_sort_order'),
            'error'      => false
        ];
    }
}