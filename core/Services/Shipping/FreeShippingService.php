<?php

namespace Alpha\Services\Shipping;

/**
 * FreeShippingService - Lógica de cálculo para o método de frete grátis.
 *
 * Verifica se o valor do carrinho atinge o mínimo configurado,
 * retornando dados brutos para a camada de apresentação (Action/Twig).
 */
class FreeShippingService
{
    /**
     * Verifica se o frete grátis é aplicável e retorna os dados brutos da cotação.
     *
     * @param float $cartTotal   Valor total do carrinho.
     * @param float $minTotal    Valor mínimo para frete grátis (vindo da config).
     * @param int   $sortOrder   Ordem de exibição.
     * @return array|null Dados brutos da cotação ou null se não aplicável.
     */
    public function getQuote(
        float $cartTotal,
        float $minTotal,
        int   $sortOrder = 0
    ): ?array {
        if ($cartTotal < $minTotal) {
            return null;
        }

        return [
            'code'         => 'free.free',
            'cost'         => 0.00,
            'tax_class_id' => 0,
            'sort_order'   => $sortOrder,
        ];
    }
}
