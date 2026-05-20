<?php

namespace Alpha\Services\Shipping;

use Opencart\System\Engine\Registry;
use Alpha\Model\Domain\Repositories\WeightClassRepository;
use Alpha\Mappers\MapperFactory;

/**
 * WeightBasedShippingService - Gerencia a lógica de cálculo para frete baseado em peso.
 * 
 * Processa faixas de preço no formato "Peso:Valor, Peso:Valor" associadas
 * a zonas geográficas, normalizando as unidades de medida via Alpha Engine.
 */
class WeightBasedShippingService
{
    private Registry $registry;
    private WeightClassRepository $weightClassRepository;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
        
        /** @var MapperFactory $mapperFactory */
        $mapperFactory = $registry->get('mapperFactory');
        $this->weightClassRepository = $mapperFactory->get(WeightClassRepository::class);
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
        $config = $this->registry->get('config');
        $db = $this->registry->get('db');
        $language = $this->registry->get('language');
        $currency = $this->registry->get('currency');
        $session = $this->registry->get('session');

        $language->load('extension/opencart/shipping/weight');

        // Normaliza o peso do carrinho para a unidade padrão da loja (Ex: Converte gramas para Kg)
        $storeWeightClassId = (int)$config->get('config_weight_class_id');
        $normalizedWeight = $this->weightClassRepository->convert($totalWeight, $weightClassId, $storeWeightClassId);

        $quote_data = [];

        // Todo: No futuro, substituir a chamada $db direta por um GeoZoneRepository
        $query = $db->query("SELECT geo_zone_id, name FROM " . DB_PREFIX . "geo_zone");

        foreach ($query->rows as $result) {
            if ($config->get('shipping_weight_' . $result['geo_zone_id'] . '_status')) {
                
                $query_zone = $db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

                if ($query_zone->num_rows) {
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
                        // Instancia a classe de formatação do motor antigo para exibir "Frete por Peso (10kg)"
                        $weightText = $this->registry->get('weight')->format($normalizedWeight, $storeWeightClassId);
                        
                        $quote_data['weight_' . $result['geo_zone_id']] = [
                            'code'         => 'weight.weight_' . $result['geo_zone_id'],
                            'title'        => $result['name'] . '  (' . $language->get('text_weight') . ' ' . $weightText . ')',
                            'cost'         => $cost,
                            'tax_class_id' => $config->get('shipping_weight_tax_class_id'),
                            'text'         => $currency->format($this->registry->get('tax')->calculate($cost, $config->get('shipping_weight_tax_class_id'), $config->get('config_tax')), $session->data['currency'])
                        ];
                    }
                }
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