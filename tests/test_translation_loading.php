<?php

require '/var/www/html/agsonhos/config.php';
require '/var/www/html/agsonhos/vendor/autoload.php';

use Alpha\Support\Language;

// Instanciar o novo Translator com pt-br
$translator = new Language('pt-br');

echo "=== 1. Testando Carregamento de JSON (pt-br) ===\n";
// Carregar namespace common
$flatCommon = $translator->load('common');
echo "Common carregado. Chaves retornadas: " . count($flatCommon) . "\n";
echo "Exemplo: button.back => " . ($flatCommon['button.back'] ?? 'NÃO ENCONTRADA') . "\n";
echo "get('button.back', 'common') => " . $translator->get('button.back', 'common') . "\n";
echo "get('header.login', 'common') => " . $translator->get('header.login', 'common') . "\n";

// Carregar namespace home
$flatHome = $translator->load('home');
echo "\nHome carregado. Chaves retornadas: " . count($flatHome) . "\n";
echo "Exemplo: hero.title => " . ($flatHome['hero.title'] ?? 'NÃO ENCONTRADA') . "\n";
echo "get('hero.title', 'home') => " . $translator->get('hero.title', 'home') . "\n";

// Testar fallback DRY de home para common
echo "\n=== 2. Testando Fallback DRY ===\n";
echo "Buscar button.back a partir do namespace home (deve achar no common): \n";
echo "get('button.back', 'home') => " . $translator->get('button.back', 'home') . "\n";

// Testar fallback legados PHP
echo "\n=== 3. Testando Fallback Legados (Arquivos PHP) ===\n";
$flatLegacy = $translator->load('common/header');
echo "Legacy header carregado. Chaves retornadas: " . count($flatLegacy) . "\n";
echo "get('text_shopping_cart', 'common/header') => " . $translator->get('text_shopping_cart', 'common/header') . "\n";
echo "get('text_shopping_cart') => " . $translator->get('text_shopping_cart') . "\n"; // teste de varredura global

// Testar troca de idioma
echo "\n=== 4. Testando Troca de Idioma (en-gb) ===\n";
$translator->setCode('en-gb');
echo "Idioma alterado para en-gb.\n";
echo "get('button.back', 'common') => " . $translator->get('button.back', 'common') . "\n";
echo "get('hero.title', 'home') => " . $translator->get('hero.title', 'home') . "\n";
echo "get('text_shopping_cart', 'common/header') => " . $translator->get('text_shopping_cart', 'common/header') . "\n";

// Testar lote 2 (busca e devoluções)
echo "\n=== 5. Testando Lote 2 de Tradução (product/search e account/returns) ===\n";
$translator->setCode('pt-br');
$translator->load('product/search');
$translator->load('account/returns');
echo "[pt-br] Search.headingTitle => " . $translator->get('headingTitle', 'product/search') . "\n";
echo "[pt-br] Returns.headingTitle => " . $translator->get('headingTitle', 'account/returns') . "\n";

$translator->setCode('en-gb');
echo "[en-gb] Search.headingTitle => " . $translator->get('headingTitle', 'product/search') . "\n";
echo "[en-gb] Returns.headingTitle => " . $translator->get('headingTitle', 'account/returns') . "\n";

echo "\n=== Testes Concluídos com Sucesso! ===\n";
