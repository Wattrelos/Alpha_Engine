<?php

require '/var/www/html/agsonhos/config.php';
require '/var/www/html/agsonhos/vendor/autoload.php';

use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\DTOs\OrderDataDTO;

try {
    echo "=== 1. Bootstrapping AppContainer ===\n";
    $bootstrap = AppBootstrap::boot();
    $container = $bootstrap->getContainer();

    /** @var RepositoryFactory $repositoryFactory */
    $repositoryFactory = $container->get(RepositoryFactory::class);

    /** @var OrderRepository $orderRepo */
    $orderRepo = $repositoryFactory->get(OrderRepository::class);

    echo "OrderRepository resolved successfully.\n";

    echo "=== 2. Creating a test order DTO ===\n";
    $testCustomerId = 2001;

    $orderData = [
        'store_id' => 1,
        'language_id' => 2,
        'currency_id' => 1,
        'customer_id' => $testCustomerId,
        'customer_group_id' => 1,
        'firstname' => 'João',
        'lastname' => 'Silva',
        'email' => 'joao@example.com',
        'telephone' => '11999999999',
        
        // New Payment Address Fields
        'payment_firstname' => 'João',
        'payment_lastname' => 'Silva',
        'payment_company' => 'Empresa Teste',
        'payment_street' => 'Rua de Teste',
        'payment_number' => 123,
        'payment_complement' => 'Sala A',
        'payment_district' => 'Centro',
        'payment_city' => 'São Paulo',
        'payment_postcode' => '01001-000',
        'payment_country' => 'Brasil',
        'payment_country_id' => 76,
        'payment_zone' => 'São Paulo',
        'payment_zone_id' => 31,
        'payment_method' => 'Pagar na Entrega',
        'payment_code' => 'cod',

        // New Shipping Address Fields
        'shipping_firstname' => 'João',
        'shipping_lastname' => 'Silva',
        'shipping_company' => 'Empresa Teste',
        'shipping_street' => 'Rua de Entrega',
        'shipping_number' => 456,
        'shipping_complement' => 'Casa 1',
        'shipping_district' => 'Bairro Novo',
        'shipping_city' => 'Campinas',
        'shipping_postcode' => '13000-000',
        'shipping_country' => 'Brasil',
        'shipping_country_id' => 76,
        'shipping_zone' => 'São Paulo',
        'shipping_zone_id' => 31,
        'shipping_method' => 'Retirar na Loja',
        'shipping_code' => 'pickup',

        // Products, totals, and total
        'products' => [
            [
                'product_id' => 1,
                'name' => 'Produto Teste',
                'model' => 'PT1',
                'quantity' => 1,
                'price' => 10.0,
                'total' => 10.0,
                'tax' => 0.0,
                'reward' => 0
            ]
        ],
        'totals' => [
            [
                'code' => 'sub_total',
                'title' => 'Sub-Total',
                'value' => 10.0,
                'sort_order' => 1
            ],
            [
                'code' => 'total',
                'title' => 'Total',
                'value' => 10.0,
                'sort_order' => 9
            ]
        ],
        'total' => 10.0,
        'comment' => 'Comentário de teste',
        'ip' => '127.0.0.1',
        'user_agent' => 'PHP Integration Test'
    ];

    $orderDto = new OrderDataDTO($orderData);

    if (!$orderDto->isValid()) {
        throw new \Exception("Order DTO is not valid!");
    }

    echo "=== 3. Saving test order ===\n";
    $savedOrderId = $orderRepo->save($orderDto);
    echo "Order saved with ID: $savedOrderId\n";

    echo "=== 3.5 Confirming order (setting status > 0) ===\n";
    $orderRepo->confirm($savedOrderId, 1, 'Confirming test order');

    echo "=== 4. Retrieving test order ===\n";
    $retrievedOrder = $orderRepo->getOrder($savedOrderId, $testCustomerId);
    
    if (empty($retrievedOrder)) {
        throw new \Exception("Could not retrieve saved order with ID $savedOrderId!");
    }

    echo "Order retrieved successfully. Asserting address fields...\n";

    $assertions = [
        'payment_street' => 'Rua de Teste',
        'payment_number' => 123,
        'payment_complement' => 'Sala A',
        'payment_district' => 'Centro',
        'shipping_street' => 'Rua de Entrega',
        'shipping_number' => 456,
        'shipping_complement' => 'Casa 1',
        'shipping_district' => 'Bairro Novo',
        'payment_country' => 'Brasil',
        'payment_zone' => 'São Paulo',
        'shipping_country' => 'Brasil',
        'shipping_zone' => 'São Paulo'
    ];

    $allPassed = true;
    foreach ($assertions as $key => $expectedValue) {
        $actualValue = $retrievedOrder[$key] ?? null;
        if ((string)$actualValue !== (string)$expectedValue) {
            echo "Assertion FAILED for field '$key': Expected '$expectedValue', got '$actualValue'\n";
            $allPassed = false;
        } else {
            echo "Assertion PASSED: Field '$key' matches '$expectedValue'\n";
        }
    }

    if ($allPassed) {
        echo "=== Integration Test PASSED! ===\n";
    } else {
        echo "=== Integration Test FAILED! ===\n";
    }

    echo "=== 5. Cleaning up test order ===\n";
    // Using DAO from OrderMapper directly to delete the order and cascade associations
    /** @var \Alpha\Mappers\EntityMappers\OrderMapper $orderMapper */
    $orderMapper = $container->get(\Alpha\Mappers\MapperFactory::class)->get(\Alpha\Mappers\EntityMappers\OrderMapper::class);
    $deleted = $orderMapper->delete($savedOrderId);
    echo "Order clean-up status: " . ($deleted ? "SUCCESS" : "FAILED") . "\n";

    echo "=== Test Completed! ===\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
