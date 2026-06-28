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
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Model\Domain\DTOs\OrderDataDTO;

require '/var/www/html/agsonhos/vendor/autoload.php';

define('APPLICATION', 'admin');
require_once '/var/www/html/agsonhos/config.php';

$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();

echo "==================================================\n";
echo "INICIANDO TESTES DO CAIXA PDV (POS CASHIER)\n";
echo "==================================================\n\n";

// 1. Verificar compilação das novas classes Action do Caixa
echo "1. Verificando as novas classes de controle (Actions):\n";
$actions = [
    \Alpha\Admin\Controllers\Actions\POS\ShowCashierDashboardAction::class,
    \Alpha\Admin\Controllers\Actions\POS\GetPreOrderAction::class,
    \Alpha\Admin\Controllers\Actions\POS\PayOrderAction::class,
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

// 2. Preparando um pedido de teste (Pendente - status 1)
echo "2. Criando pedido de teste (Pendente - status 1):\n";

/** @var ProductRepository $productRepo */
$productRepo = RepositoryFactory::getInstance()->get(ProductRepository::class);
$products = $productRepo->getProducts(['filter_name' => '', 'start' => 0, 'limit' => 1]);

if (empty($products)) {
    echo "  [FAIL] Nenhum produto ativo encontrado no catálogo para teste!\n";
    exit(1);
}

$testProduct = reset($products);
$productId = (int)($testProduct['product_id'] ?? $testProduct['id']);
$initialQty = (int)($testProduct['quantity'] ?? 0);

/** @var CustomerRepository $customerRepo */
$customerRepo = RepositoryFactory::getInstance()->get(CustomerRepository::class);
$customers = $customerRepo->findAll();
$testCustomer = !empty($customers) ? reset($customers) : null;

$customerId = $testCustomer ? $testCustomer->getId() : 0;
$firstname = $testCustomer ? $testCustomer->getFirstname() : 'Cliente';
$lastname = $testCustomer ? $testCustomer->getLastname() : 'PDV';
$email = $testCustomer ? $testCustomer->getEmail() : 'cliente.pdv@agsonhos.com';

$price = (float)($testProduct['special'] ?: $testProduct['price']);
$total = $price * 1;

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
    'order_status_id'       => 1, // Pendente
    'store_name'            => 'AgSonhos PDV Teste Caixa',
    'store_url'             => 'http://localhost/',
    'customer_group_id'     => 1,
    'language_id'           => 2,
    'language_code'         => 'pt-br',
    'currency_id'           => 1,
    'currency_code'         => 'BRL',
    'currency_value'        => 1.0,
    'ip'                    => '127.0.0.1',
    'user_agent'            => 'TestAgent',
    'products'              => [
        [
            'product_id' => $productId,
            'name'       => $testProduct['name'],
            'model'      => $testProduct['model'] ?? '',
            'quantity'   => 1,
            'price'      => $price,
            'total'      => $total,
            'tax'        => 0.0,
            'reward'     => 0,
        ]
    ],
    'totals'                => [
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
    ],
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
    'comment'               => 'Pedido gerado pelo script de testes do Caixa.',
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
    $orderId = $uow->transaction(function() use ($orderRepo, $productRepo, $orderDto, $productId, $initialQty) {
        $id = $orderRepo->save($orderDto);
        
        // Simula a dedução inicial do estoque na criação do pré-pedido
        $productMapper = MapperFactory::getInstance()->get(ProductMapper::class);
        $productMapper->updateQuantity($productId, $initialQty - 1);
        
        return $id;
    });
    echo "  [OK] Pedido de teste criado com sucesso! Order ID: {$orderId}\n\n";

    // 3. Testar a busca do pedido por ID (GetPreOrderAction simulação)
    echo "3. Simulando consulta de pré-venda (GetPreOrderAction):\n";
    $fetchedOrder = $orderRepo->getOrder($orderId);
    if (!empty($fetchedOrder)) {
        echo "  [OK] Pedido localizado no banco! Status ID: {$fetchedOrder['order_status_id']}\n";
        if ((int)$fetchedOrder['order_status_id'] === 1) {
            echo "  [OK] Status está correto (1 - Pendente).\n";
        } else {
            echo "  [FAIL] Status incorreto! Esperado: 1, Obtido: {$fetchedOrder['order_status_id']}\n";
            exit(1);
        }
    } else {
        echo "  [FAIL] Pedido não encontrado pelo OrderRepository!\n";
        exit(1);
    }
    echo "\n";

    // 4. Testar a finalização do pagamento (PayOrderAction simulação)
    echo "4. Simulando confirmação de pagamento e transição de status (PayOrderAction):\n";
    $uow->transaction(function() use ($orderRepo, $orderId) {
        $orderRepo->confirm($orderId, 5, 'Pagamento processado via script de testes (Pix)');
    });

    // Validar se o status do pedido mudou para Completo (5)
    $fetchedOrderAfter = $orderRepo->getOrder($orderId);
    $statusAfter = (int)$fetchedOrderAfter['order_status_id'];
    if ($statusAfter === 5) {
        echo "  [OK] Transição de status efetuada com sucesso: 1 (Pendente) -> 5 (Completo)\n";
    } else {
        echo "  [FAIL] Transição falhou! Status obtido: {$statusAfter}\n";
        exit(1);
    }
    
    // Validar se a quantidade de estoque permaneceu reservada (não houve dupla dedução)
    $stmt = ConnectionDB::getInstance()->getConnection()->prepare("SELECT quantity FROM `" . DB_PREFIX . "product` WHERE id = ?");
    $stmt->execute([$productId]);
    $qtyAfterPay = (int)$stmt->fetchColumn();
    
    if ($qtyAfterPay === ($initialQty - 1)) {
        echo "  [OK] Integridade do estoque mantida (sem dupla dedução): {$qtyAfterPay} unidades\n";
    } else {
        echo "  [FAIL] Falha na integridade de estoque! Qtd antes do pagamento: " . ($initialQty - 1) . ", Qtd após pagamento: {$qtyAfterPay}\n";
        exit(1);
    }
    echo "\n";

    // 5. Limpeza de dados do teste
    echo "5. Limpando dados de teste do banco de dados:\n";
    $uow->transaction(function() use ($orderId, $productId, $initialQty) {
        /** @var OrderMapper $orderMapper */
        $orderMapper = MapperFactory::getInstance()->get(OrderMapper::class);
        $orderMapper->delete($orderId);
        
        /** @var ProductMapper $productMapper */
        $productMapper = MapperFactory::getInstance()->get(ProductMapper::class);
        $productMapper->updateQuantity($productId, $initialQty);
    });

    // Confirmar se o estoque foi restaurado
    $stmt = ConnectionDB::getInstance()->getConnection()->prepare("SELECT quantity FROM `" . DB_PREFIX . "product` WHERE id = ?");
    $stmt->execute([$productId]);
    $qtyRestored = (int)$stmt->fetchColumn();
    if ($qtyRestored === $initialQty) {
        echo "  [OK] Banco de dados limpo e estoque restaurado com sucesso!\n";
    } else {
        echo "  [WARNING] Falha ao restaurar a quantidade original de estoque. Esperado: {$initialQty}, Obtido: {$qtyRestored}\n";
    }

} catch (\Exception $e) {
    echo "  [FAIL] Erro ocorrido durante a execução: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n==================================================\n";
echo "TODOS OS TESTES DO CAIXA PASSARAM COM SUCESSO!\n";
echo "==================================================\n";
