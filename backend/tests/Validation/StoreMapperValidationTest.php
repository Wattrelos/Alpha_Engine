<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Alpha\Mappers\EntityMappers\StoreMapper;
use Alpha\Mappers\MapperFactory;
use Alpha\Support\Cache\CacheStrategyInterface;

class StoreMapperValidationTest extends TestCase
{
    private StoreMapper $mapper;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'catalog');
        }
        $bootstrap = AppBootstrap::boot();
        $container = $bootstrap->getContainer();
        /** @var MapperFactory $factory */
        $factory = $container->get('alpha_mapper_factory');
        $this->mapper = $factory->get(StoreMapper::class);
    }

    public function testStoreMapperHasCachePropertyDefined(): void
    {
        $this->assertInstanceOf(CacheStrategyInterface::class, $this->mapper->getCache());
    }

    public function testGetStoresExecutesWithoutUndefinedPropertyError(): void
    {
        $stores = $this->mapper->getStores();
        $this->assertIsArray($stores);
    }
}
