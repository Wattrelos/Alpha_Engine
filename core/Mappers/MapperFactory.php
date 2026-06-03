<?php

namespace Alpha\Mappers;

use Containers\AppContainer;

/**
 * MapperFactory - Centraliza a criação de Mappers na Alpha Engine.
 *
 * Objetivo: Facilitar a migração de controladores legados, removendo a necessidade
 * de instanciar mappers manualmente com a conexão de banco de dados.
 */
class MapperFactory
{
    private array $instances = [];
    private static ?self $instance = null;

    public function __construct(private ?AppContainer $container = null) {
        self::$instance = $this;
    }

    /**
     * Obtém a instância global da Factory.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \Exception("Alpha Engine Error: MapperFactory não foi inicializado.");
        }
        return self::$instance;
    }

    /**
     * Obtém uma instância única de um Mapper.
     *
     * @param string $className Nome da classe do Mapper
     * @return mixed
     */
    public function get(string $className): mixed
    {
        if (!isset($this->instances[$className])) {
            $this->instances[$className] = new $className($this->container);
        }
        return $this->instances[$className];
    }
}