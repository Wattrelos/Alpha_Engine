<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('APPLICATION')) {
    define('APPLICATION', 'catalog');
}
require_once __DIR__ . '/../config.php';

use Containers\AppBootstrap;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Mappers\MapperFactory;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

$bootstrap = AppBootstrap::boot();

echo "=== INICIANDO SUÍTE DE TESTES: FULL-TEXT SEARCH ===\n\n";

$mapper = MapperFactory::getInstance()->get(ProductMapper::class);
$productRepo = RepositoryFactory::getInstance()->get(ProductRepository::class);

// Teste 1: Formatação e Sanitização de Termos Booleanos
echo "[TEST 1] Sanitização e Formatação de Termos\n";
$testCases = [
    'smart tv'         => '+smart* +tv*',
    'cama  casal'      => '+cama* +casal*',
    'smart + tv - red' => '+smart* +tv* +red*',
    'travesseiro'      => '+travesseiro*'
];

foreach ($testCases as $input => $expected) {
    $result = $mapper->prepareFullTextQuery($input);
    echo "  Input: '$input' => Output: '$result'\n";
    if ($result !== $expected) {
        echo "  [FAIL] Esperado '$expected', mas obteve '$result'\n";
        exit(1);
    }
}
echo "  [PASS] Sanitização de termos funcionou como esperado.\n\n";

// Teste 2: Busca por palavra inteira/parcial (getProducts & getTotalProducts)
echo "[TEST 2] Execução da Busca Full-Text no Banco de Dados com Dados Reais\n";
$filterData = [
    'filter_name' => 'Adaptador',
    'start'       => 0,
    'limit'       => 10
];

$products = $mapper->getProducts($filterData, 2, 1, 1);
$total = $mapper->getTotalProducts($filterData, 2, 1);

echo "  Busca por 'Adaptador': " . count($products) . " produtos retornados na página. Total no catálogo: $total\n";
if ($total === 0) {
    echo "  [FAIL] Esperava encontrar produtos para 'Adaptador'\n";
    exit(1);
}
echo "  [PASS] Execução de query FULLTEXT retornou resultados reais do banco de dados.\n\n";

// Teste 3: Fallback para termos curtos (< 3 caracteres)
echo "[TEST 3] Fallback para Termo Curto (ex: 'TV')\n";
$filterShort = [
    'filter_name' => 'TV',
    'start'       => 0,
    'limit'       => 10
];

$productsShort = $mapper->getProducts($filterShort, 2, 1, 1);
$totalShort = $mapper->getTotalProducts($filterShort, 2, 1);
echo "  Busca por 'TV': " . count($productsShort) . " produtos retornados. Total: $totalShort\n";
echo "  [PASS] Fallback de termo curto funcionou sem erros.\n\n";

// Teste 4: Integração via ProductRepository::getSearchData
echo "[TEST 4] Teste de Integração no ProductRepository::getSearchData()\n";
$viewResponse = $productRepo->getSearchData([
    'search' => 'Tubo',
    'limit'  => 5
]);

$data = $viewResponse->getData();
echo "  Produtos encontrados via Repository: " . count($data['products'] ?? []) . "\n";
echo "  Total no DTO: " . ($data['product_total'] ?? 0) . "\n";
if (($data['product_total'] ?? 0) === 0) {
    echo "  [FAIL] Esperava encontrar produtos via Repository para 'Tubo'\n";
    exit(1);
}
echo "  [PASS] ProductRepository respondeu corretamente.\n\n";

echo "=== TODOS OS TESTES PASSARAM COM SUCESSO! ===\n";
