<?php

use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Mappers\EntityMappers\OrderMapper;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Mappers\MapperFactory;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Model\Domain\DTOs\OrderDataDTO;

require '/var/www/html/agsonhos/vendor/autoload.php';

define('APPLICATION', 'admin');
require_once '/var/www/html/agsonhos/config.php';

$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();

echo "==================================================\n";
echo "INICIANDO TESTES DO PDV (POINT OF SALE)\n";
echo "==================================================\n\n";

// 1. Verificar compilação das novas classes Action
echo "1. Verificando as classes de controle (Actions):\n";
$actions = [
    \Alpha\Admin\Controllers\Actions\POS\ShowSalesRepDashboardAction::class,
    \Alpha\Admin\Controllers\Actions\POS\ShowSalesRepCheckoutAction::class,
    \Alpha\Admin\Controllers\Actions\POS\SearchProductAction::class,
    \Alpha\Admin\Controllers\Actions\POS\SearchCustomerAction::class,
    \Alpha\Admin\Controllers\Actions\POS\CreatePreOrderAction::class,
];

foreach ($actions as $action) {
    if (class_exists($action)) {
        echo "  [OK] Class compiled and loaded: {$action}\n";
    } else {
        echo "  [FAIL] Class not found: {$action}\n";
        exit(1);
    }
}
echo "\n";

// 2. Testar busca de produtos
echo "2. Testando busca de produtos via ProductRepository:\n";
/** @var ProductRepository $productRepo */
$productRepo = RepositoryFactory::getInstance()->get(ProductRepository::class);
$products = $productRepo->getProducts(['filter_name' => '', 'start' => 0, 'limit' => 1]);

if (!empty($products)) {
    $testProduct = reset($products);
    $prodId = (int)($testProduct['product_id'] ?? $testProduct['id'] ?? 0);
    $prodQty = (int)($testProduct['quantity'] ?? 0);
    echo "  [OK] Encontrou produto para teste: '{$testProduct['name']}' (ID: {$prodId}, Qtd: {$prodQty})\n";
} else {
    echo "  [FAIL] Nenhum produto ativo encontrado no catálogo para teste!\n";
    exit(1);
}
echo "\n";

// 3. Testar busca de cliente (ou criar um cliente dummy para o teste)
echo "3. Testando busca de clientes via CustomerRepository:\n";
/** @var CustomerRepository $customerRepo */
$customerRepo = RepositoryFactory::getInstance()->get(CustomerRepository::class);
$customers = $customerRepo->findAll();

$testCustomer = null;
if (!empty($customers)) {
    $testCustomer = reset($customers);
    echo "  [OK] Encontrou cliente para teste: '{$testCustomer->getFirstname()} {$testCustomer->getLastname()}' (ID: {$testCustomer->getId()})\n";
} else {
    echo "  [INFO] Nenhum cliente encontrado. Usaremos ID 0 (Consumidor Não Identificado).\n";
}
echo "\n";

// 4. Simular criação de pré-venda e reserva de estoque com UnitOfWork transacional
echo "4. Simulando criação de Pré-Venda e Reserva de Estoque:\n";

$productId = $prodId;
$initialQty = $prodQty;
$qtyToOrder = 1;
$expectedNewQty = $initialQty - $qtyToOrder;

$customerId = $testCustomer ? $testCustomer->getId() : 0;
$firstname = $testCustomer ? $testCustomer->getFirstname() : 'Cliente';
$lastname = $testCustomer ? $testCustomer->getLastname() : 'PDV';
$email = $testCustomer ? $testCustomer->getEmail() : 'cliente.pdv@agsonhos.com';

$price = (float)($testProduct['special'] ?: $testProduct['price']);
$total = $price * $qtyToOrder;

$productsData = [
    [
        'product_id' => $productId,
        'name'       => $testProduct['name'],
        'model'      => $testProduct['model'] ?? '',
        'quantity'   => $qtyToOrder,
        'price'      => $price,
        'total'      => $total,
        'tax'        => 0.0,
        'reward'     => 0,
    ]
];

$totalsData = [
    [
        'code'       => 'sub_total',
        'title'      => 'Sub-Total',
        'value'      => $total,
        'sort_order' => 1,
    ],
    [
        'code'       => 'total',
        'title'      => 'Total',
        'value'      => $total,
        'sort_order' => 9,
    ],
];

$orderData = [
    'store_id'              => 1,
    'customer_id'           => $customerId,
    'firstname'             => $firstname,
    'lastname'              => $lastname,
    'email'                 => $email,
    'telephone'             => $testCustomer ? $testCustomer->getTelephone() : '',
    'payment_method'        => 'PDV - Pendente',
    'shipping_method'       => 'Retirada no Balcão',
    'total'                 => $total,
    'order_status_id'       => 1, // Status 1 = Pendente
    'store_name'            => 'AgSonhos PDV Teste',
    'store_url'             => 'http://localhost/',
    'customer_group_id'     => 1,
    'language_id'           => 2,
    'language_code'         => 'pt-br',
    'currency_id'           => 1,
    'currency_code'         => 'BRL',
    'currency_value'        => 1.0,
    'ip'                    => '127.0.0.1',
    'user_agent'            => 'TestAgent',
    'products'              => $productsData,
    'totals'                => $totalsData,
    'payment_firstname'     => $firstname,
    'payment_lastname'      => $lastname,
    'payment_company'       => '',
    'payment_street'        => '',
    'payment_number'        => 0,
    'payment_complement'    => '',
    'payment_district'      => '',
    'payment_city'          => '',
    'payment_postcode'      => '',
    'payment_country'       => '',
    'payment_country_id'    => 0,
    'payment_zone'          => '',
    'payment_zone_id'       => 0,
    'payment_address_format'=> '',
    'shipping_firstname'    => $firstname,
    'shipping_lastname'     => $lastname,
    'shipping_company'      => '',
    'shipping_street'       => '',
    'shipping_number'       => 0,
    'shipping_complement'   => '',
    'shipping_district'     => '',
    'shipping_city'         => '',
    'shipping_postcode'     => '',
    'shipping_country'      => '',
    'shipping_country_id'   => 0,
    'shipping_zone'         => '',
    'shipping_zone_id'      => 0,
    'shipping_address_format'=> '',
    'comment'               => 'Pedido gerado pelo script de testes.',
    'tracking'              => '',
    'forwarded_ip'          => '',
    'accept_language'       => '',
    'payment_address_id'    => 0,
    'shipping_address_id'   => 0,
    'invoice_prefix'        => 'INV-',
];

$orderDto = new OrderDataDTO($orderData);

/** @var OrderRepository $orderRepo */
$orderRepo = RepositoryFactory::getInstance()->get(OrderRepository::class);
$uow = new UnitOfWork();

try {
    $orderId = $uow->transaction(function() use ($orderRepo, $productRepo, $orderDto, $productsData) {
        $id = $orderRepo->save($orderDto);
        
        $productMapper = MapperFactory::getInstance()->get(ProductMapper::class);
        foreach ($productsData as $p) {
            $product = $productRepo->getProduct($p['product_id']);
            if ($product) {
                $currentQty = (int)($product['quantity'] ?? 0);
                $newQty = $currentQty - (int)$p['quantity'];
                $productMapper->updateQuantity($p['product_id'], $newQty);
            }
        }
        return $id;
    });
    
    echo "  [OK] Pré-venda registrada com sucesso! Order ID: {$orderId}\n";
    
    // Validar estoque atualizado via query direta ao banco de dados
    $stmt = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection()->prepare("SELECT quantity FROM `" . DB_PREFIX . "product` WHERE id = ?");
    $stmt->execute([$productId]);
    $qtyAfter = (int)$stmt->fetchColumn();
    
    if ($qtyAfter === $expectedNewQty) {
        echo "  [OK] Estoque deduzido com sucesso: {$initialQty} -> {$qtyAfter}\n";
    } else {
        echo "  [FAIL] Estoque incorreto! Esperado: {$expectedNewQty}, Obtido: {$qtyAfter}\n";
        exit(1);
    }
    
    // 5. Limpeza de dados (Deletar pedido de teste e restaurar estoque)
    echo "\n5. Limpando dados de teste do banco de dados:\n";
    $uow->transaction(function() use ($orderId, $productId, $initialQty) {
        /** @var OrderMapper $orderMapper */
        $orderMapper = MapperFactory::getInstance()->get(OrderMapper::class);
        $orderMapper->delete($orderId);
        
        /** @var ProductMapper $productMapper */
        $productMapper = MapperFactory::getInstance()->get(ProductMapper::class);
        $productMapper->updateQuantity($productId, $initialQty);
    });
    
    // Confirmar se o estoque foi restaurado via query direta
    $stmt = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection()->prepare("SELECT quantity FROM `" . DB_PREFIX . "product` WHERE id = ?");
    $stmt->execute([$productId]);
    $qtyRestored = (int)$stmt->fetchColumn();
    if ($qtyRestored === $initialQty) {
        echo "  [OK] Banco de dados limpo e estoque restaurado com sucesso!\n";
    } else {
        echo "  [WARNING] Falha ao restaurar a quantidade original de estoque. Esperado: {$initialQty}, Obtido: {$qtyRestored}\n";
    }
    
} catch (\Exception $e) {
    echo "  [FAIL] Erro transacional ocorrido: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n==================================================\n";
echo "TODOS OS TESTES DO PDV PASSARAM COM SUCESSO!\n";
echo "==================================================\n";
