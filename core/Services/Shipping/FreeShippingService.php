<?php

namespace Alpha\Services\Shipping;

use Alpha\Support\Registry;

/**
 * FreeShippingService - Gerencia a lógica de cálculo para o método de frete grátis.
 * 
 * Implementa a regra de negócio para oferecer frete grátis com base no valor
 * total do carrinho e na zona geográfica.
 */
class FreeShippingService
{
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Verifica se o frete grátis é aplicável e retorna a cotação.
     * 
     * @param array $address Endereço de entrega.
     * @param float $cartTotal Valor total do carrinho para comparação.
     * @return array|null Dados da cotação ou null se não aplicável.
     */
    public function getQuote(array $address, float $cartTotal): ?array
    {
        $config = $this->registry->get('config');
        $language = $this->registry->get('language');
        $currency = $this->registry->get('currency');
        $session = $this->registry->get('session');

        // Regra Alpha Engine: Verifica o valor mínimo para frete grátis
        $minTotal = (float)$config->get('shipping_free_total');
        
        if ($cartTotal < $minTotal) {
            return null; // Não atingiu o valor mínimo
        }

        $language->load('extension/opencart/shipping/free');

        // Retorno formatado seguindo o padrão OpenCart para compatibilidade transparente com o checkout
        return [
            'code'       => 'free.free',
            'title'      => $language->get('text_title') ?: 'Frete Grátis',
            'quote'      => [
                'free' => [
                    'code'         => 'free.free',
                    'title'        => $language->get('text_description') ?: 'Frete Grátis',
                    'cost'         => 0.00,
                    'tax_class_id' => 0,
                    'text'         => $currency->format(0.00, $session->data['currency'])
                ]
            ],
            'sort_order' => (int)$config->get('shipping_free_sort_order'),
            'error'      => false
        ];
    }
}
