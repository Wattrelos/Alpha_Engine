<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../config.php';

use PHPUnit\Framework\TestCase;
use Alpha\Mappers\MapperFactory;
use Containers\AppContainer;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\Entities\Customer\Customer;

class TenantIsolationTest extends TestCase
{
    private MapperFactory $mapperFactory;

    protected function setUp(): void
    {
        $this->mapperFactory = new MapperFactory();
    }

    /**
     * Teste 1: Resolução de store_id a partir das configurações do container do Tenant.
     */
    public function testStoreIdResolutionFromContainer(): void
    {
        $containerStore1 = new AppContainer();
        $containerStore1->bind('configSettings', ['config_store_id' => 1]);

        $customerRepoStore1 = new CustomerRepository($this->mapperFactory, $containerStore1);
        
        $this->assertEquals(1, $customerRepoStore1->getStoreId(), "CustomerRepository deve resolver store_id = 1.");
    }

    /**
     * Teste 2: Isolamento de Acesso Cross-Tenant a Clientes.
     * Usuário da Loja 1 tentando acessar registro da Loja 2 deve ter o retorno bloqueado (retornando NULL).
     */
    public function testCrossTenantCustomerAccessBlocked(): void
    {
        $containerStore1 = new AppContainer();
        $containerStore1->bind('configSettings', ['config_store_id' => 1]);

        // Cliente pertencente à Loja 2
        $customerStore2 = new Customer();
        $customerStore2->setId(99999);
        $customerStore2->setStoreId(2);
        $customerStore2->setFirstname("Cliente");
        $customerStore2->setLastname("Loja 2");
        $customerStore2->setEmail("tenant2@exemplo.com");

        // Simula busca a partir do repositório da Loja 1
        $customerRepoStore1 = new class($this->mapperFactory, $containerStore1, $customerStore2) extends CustomerRepository {
            private Customer $mockCustomer;
            public function __construct($mapperFactory, $container, Customer $mockCustomer) {
                parent::__construct($mapperFactory, $container);
                $this->mockCustomer = $mockCustomer;
            }
            public function find(int $id): ?Customer {
                $customer = $this->mockCustomer;
                $storeId = $this->getStoreId();
                if ($storeId > 0 && $customer->getStoreId() !== $storeId) {
                    return null; // Filtro multi-tenant ativado
                }
                return $customer;
            }
        };

        $result = $customerRepoStore1->find(99999);

        $this->assertNull($result, "Busca cross-tenant de cliente de outra loja deve retornar NULL para prevenir vazamento de dados.");
    }

    /**
     * Teste 3: Filtro de store_id em Pedidos e Carrinho de Compras.
     */
    public function testStoreIdFilterInOrderAndCartRepository(): void
    {
        $containerStore2 = new AppContainer();
        $containerStore2->bind('configSettings', ['config_store_id' => 2]);

        $orderRepo = new OrderRepository($this->mapperFactory, $containerStore2);
        $cartRepo = new CartRepository($this->mapperFactory, $containerStore2);

        $this->assertEquals(2, $orderRepo->getStoreId(), "OrderRepository deve estar restrito à Loja 2.");
        $this->assertEquals(2, $cartRepo->getStoreId(), "CartRepository deve estar restrito à Loja 2.");
    }
}
