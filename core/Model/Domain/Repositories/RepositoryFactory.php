<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\MapperFactory;
use Containers\AppContainer;

class RepositoryFactory
{
    private array $instances = [];
    private static ?self $instance = null;
    private ?\Alpha\Support\Cache\CacheStrategyInterface $cache = null;

    public function __construct(private MapperFactory $mapperFactory, private AppContainer $container)
    {
        self::$instance = $this;
        $this->cache = new \Alpha\Support\Cache\FilesystemCacheStrategy();
    }

    /**
     * Obtém a instância global da Factory de Repositórios.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \Exception("Alpha Engine Error: RepositoryFactory não foi inicializado.");
        }
        return self::$instance;
    }

    public function get(string $className): mixed
    {
        if (!isset($this->instances[$className])) {
            $this->instances[$className] = new $className($this->mapperFactory, $this->container, $this->cache);
        }
        return $this->instances[$className];
    }
}
