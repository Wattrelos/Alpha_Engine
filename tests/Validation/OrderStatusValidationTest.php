<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\OrderStatusRepository;

class OrderStatusValidationTest extends TestCase
{
    private OrderStatusRepository $repo;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'catalog');
        }
        $bootstrap = AppBootstrap::boot();
        $container = $bootstrap->getContainer();
        /** @var \Alpha\Model\Domain\Repositories\RepositoryFactory $factory */
        $factory = $container->get('alpha_repository_factory');
        $this->repo = $factory->get(OrderStatusRepository::class);
    }

    public function testGetOrderStatusesReturnsNonEmptyList(): void
    {
        $statuses = $this->repo->getOrderStatuses();
        $this->assertNotEmpty($statuses, "Deve retornar a lista de status de pedidos cadastrados.");
    }

    public function testGetOrderStatusById(): void
    {
        $status = $this->repo->getOrderStatus(1);
        $this->assertNotEmpty($status, "Deve encontrar o status de pedido com ID 1.");

        $statusEntity = $this->repo->find(1);
        $this->assertNotNull($statusEntity, "Entidade de status de pedido com ID 1 deve ser encontrada.");
        $this->assertEquals(1, $statusEntity->getId());
    }
}
