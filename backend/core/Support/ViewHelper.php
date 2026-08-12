<?php

namespace Alpha\Support;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * ViewHelper - Centraliza a geração de fragmentos de HTML reutilizáveis.
 * Implementado como Twig Extension para disponibilidade global nos templates.
 */
class ViewHelper extends AbstractExtension
{
    /**
     * Registra as funções que estarão disponíveis globalmente no Twig.
     * Ex: {{ render_badges(product) }}
     * 
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            // 'is_safe' => ['html'] indica que o Twig não deve escapar o HTML retornado
            new TwigFunction('render_badges', [$this, 'renderProductBadges'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Renderiza os selos (badges) de um produto de forma padronizada.
     * 
     * @param array $product Dados do produto formatados pelo ProductShowcaseDTO.
     * @return string HTML renderizado.
     */
    public function renderProductBadges(array $product): string
    {
        $html = '';

        // Badge de Promoção (Sale)
        if (!empty($product['is_sale'])) {
            $label = !empty($product['discount']) ? $product['discount'] : 'OFF';
            $html .= '<span class="badge badge-sale">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
        }

        // Badge de Novidade (New)
        if (!empty($product['is_new'])) {
            $html .= '<span class="badge badge-new">NEW</span>';
        }

        return $html ? '<div class="product-badges-container">' . $html . '</div>' : '';
    }
}