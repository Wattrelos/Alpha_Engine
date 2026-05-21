<?php
/**
 * Alpha Engine
 * Script para arquivar controllers legados que não são mais roteados no sistema.
 */

$baseDir = dirname(__DIR__, 2) . '/catalog/controller/';

$controllersPortados = [
    // Adicione aqui controladores que você tem 100% de certeza que não respondem mais 
    // por nenhuma URL (route=...) válida na loja.
    // IMPORTANTE: Não adicione os controllers de páginas ativas (como product/category.php 
    // ou checkout/cart.php), pois eles ainda são os pontos de entrada do OpenCart, 
    // mesmo rodando via Alpha Engine!
    /*
    'startup/exemplo_obsoleto.php',
    */
];

foreach ($controllersPortados as $arquivo) {
    $caminhoAtual = $baseDir . $arquivo;
    $caminhoOld = $caminhoAtual . '.old';

    if (file_exists($caminhoAtual)) {
        rename($caminhoAtual, $caminhoOld);
        echo "✅ Arquivado: {$arquivo} -> {$arquivo}.old\n";
    } else {
        echo "⚠️ Ignorado (não encontrado ou já arquivado): {$arquivo}\n";
    }
}

echo "\n🚀 Limpeza de controllers legados concluída!\n";