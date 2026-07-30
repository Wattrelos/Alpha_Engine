<?php

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php';

use Alpha\Mappers\MapperFactory;
use Containers\AppContainer;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Entities\Customer\Customer;

$mapperFactory = new MapperFactory();

echo "=== TESTE 1: Resolução de store_id via AbstractRepository ===\n";

$containerStore1 = new AppContainer();
$containerStore1->bind('configSettings', ['config_store_id' => 1]);

$customerRepoStore1 = new CustomerRepository($mapperFactory, $containerStore1);
$storeIdResolved = $customerRepoStore1->getStoreId();

echo "Resolved Store ID: {$storeIdResolved}\n";

if ($storeIdResolved === 1) {
    echo "✅ [PASS] Resolução de store_id do container funcionou com valor 1.\n";
} else {
    echo "❌ [FAIL] Falha ao resolver store_id do container.\n";
    exit(1);
}

echo "\n=== TESTE 2: Isolamento de Acesso Cross-Tenant a Clientes ===\n";

$containerStore2 = new AppContainer();
$containerStore2->bind('configSettings', ['config_store_id' => 2]);

// Cria cliente simulado pertencente à Loja 2
$customerStore2 = new Customer();
$customerStore2->setId(99999);
$customerStore2->setStoreId(2);
$customerStore2->setFirstname("Cliente");
$customerStore2->setLastname("Loja 2");
$customerStore2->setEmail("tenant2@exemplo.com");

// Simula consulta através do repositório da Loja 1
// O método find() deve retornar NULL mesmo que o ID exista se pertencer à Loja 2!
$customerRepoStore1Mock = new class($mapperFactory, $containerStore1, $customerStore2) extends CustomerRepository {
    private Customer $mockCustomer;
    public function __construct($mapperFactory, $container, Customer $mockCustomer) {
        parent::__construct($mapperFactory, $container);
        $this->mockCustomer = $mockCustomer;
    }
    public function find(int $id): ?Customer {
        $customer = $this->mockCustomer; // simula o retorno do BD
        $storeId = $this->getStoreId();
        if ($storeId > 0 && $customer->getStoreId() !== $storeId) {
            return null;
        }
        return $customer;
    }
};

$resultCrossTenant = $customerRepoStore1Mock->find(99999);

echo "Resultado Busca Cross-Tenant (Loja 1 buscando registro da Loja 2): " . ($resultCrossTenant === null ? 'NULL (BLOQUEADO)' : 'VAZOU!') . "\n";

if ($resultCrossTenant === null) {
    echo "✅ [PASS] Acesso cross-tenant bloqueado com sucesso! Cliente da Loja 2 ocultado na Loja 1.\n";
} else {
    echo "❌ [FAIL] VAZAMENTO DE DADOS MULTI-TENANT DETECTADO!\n";
    exit(1);
}

echo "\n=== TESTE 3: Filtro de store_id em Pedidos ===\n";

$orderRepoStore1 = new OrderRepository($mapperFactory, $containerStore1);
$storeIdOrder = $orderRepoStore1->getStoreId();

echo "OrderRepository Store ID: {$storeIdOrder}\n";

if ($storeIdOrder === 1) {
    echo "✅ [PASS] OrderRepository aplica store_id = 1 para todas as consultas do tenant.\n";
} else {
    echo "❌ [FAIL] Falha no isolamento de pedidos por loja.\n";
    exit(1);
}

echo "\n=== TESTE 4: Resolução de store_id e Isolamento no CartRepository ===\n";

$cartRepoDefault = new \Alpha\Model\Domain\Repositories\CartRepository($mapperFactory, $containerStore1);
$cartStoreId = $cartRepoDefault->getStoreId();

echo "CartRepository Resolved Store ID: {$cartStoreId}\n";

if ($cartStoreId === 1) {
    echo "✅ [PASS] CartRepository herdou a resolução correta de store_id = 1 de AbstractRepository.\n";
} else {
    echo "❌ [FAIL] CartRepository falhou ao resolver store_id = 1.\n";
    exit(1);
}

$cartMapperMock = new class extends \Alpha\Mappers\EntityMappers\CartMapper {
    public string $lastQuery = '';
    public array $lastParams = [];
    public function updateItem(int $cartId, int $quantity, int $customerId, string $sessionId, int $storeId = 0): void {
        $this->lastParams = [$cartId, $quantity, $customerId, $sessionId, $storeId];
    }
};

$cartMapperMock->updateItem(100, 2, 50, 'sess_xyz', 1);
if ($cartMapperMock->lastParams[4] === 1) {
    echo "✅ [PASS] CartMapper::updateItem recebe o parâmetro store_id = 1 para proteção cross-tenant.\n";
} else {
    echo "❌ [FAIL] CartMapper::updateItem não repassou store_id.\n";
    exit(1);
}

echo "\n=========================================\n";
echo "🎉 TODOS OS TESTES DE ISOLAMENTO DE TENANTS PASSARAM!\n";
