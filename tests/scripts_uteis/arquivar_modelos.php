<?php
/**
 * Alpha Engine
 * Script para arquivar modelos legados que já foram portados para DDD/Mappers.
 */

$baseDir = dirname(__DIR__, 2) . '/catalog/model/';

$modelosPortados = [
    'design/banner.php',
    'design/theme.php',
    'design/translation.php',
    'setting/setting.php',

    // Já renomeados: 
    /*
    'account/address.php',
    'account/custom_field.php',
    'account/customer.php',
    'account/search.php'
    'account/wishlist.php',
    'catalog/category.php',
    'catalog/information.php',
    'catalog/manufacturer.php',
    'catalog/product.php',
    'catalog/review.php',
    'checkout/coupon.php'
    'checkout/order.php',
    'checkout/voucher.php',
    'design/layout.php',
    'design/seo_url.php',
    'localisation/currency.php',
    'localisation/geo_zone.php',
    'localisation/language.php',
    'localisation/length_class.php',
    'localisation/tax_class.php',
    'localisation/tax_rate.php',
    'localisation/tax_rule.php',
    'localisation/weight_class.php',
    'setting/extension.php',
    'setting/module.php',
    */
];

foreach ($modelosPortados as $arquivo) {
    $caminhoAtual = $baseDir . $arquivo;
    $caminhoOld = $caminhoAtual . '.old';

    if (file_exists($caminhoAtual)) {
        rename($caminhoAtual, $caminhoOld);
        echo "✅ Arquivado: {$arquivo} -> {$arquivo}.old\n";
    } else {
        echo "⚠️ Ignorado (não encontrado ou já arquivado): {$arquivo}\n";
    }
}

echo "\n🚀 Limpeza de modelos legados concluída!\n";
