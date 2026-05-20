<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\MapperFactory;
use Opencart\System\Engine\Registry;

class RepositoryFactory
{
    private array $instances = [];
    private static ?self $instance = null;

    public function __construct(private MapperFactory $mapperFactory, private Registry $registry)
    {
        self::$instance = $this;
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
            $this->instances[$className] = new $className($this->mapperFactory, $this->registry);
        }
        return $this->instances[$className];
    }
}