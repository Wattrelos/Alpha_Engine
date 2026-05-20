<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\MapperFactory;
use Alpha\Support\Cache\CacheStrategyInterface;

/**
 * AbstractRepository - Classe base para todos os Repositórios da Alpha Engine.
 * 
 * Fornece a infraestrutura necessária para a camada de domínio, incluindo
 * o acesso à fábrica de mappers e o suporte opcional à estratégia de cache.
 */
abstract class AbstractRepository
{
    protected MapperFactory $mapperFactory;
    protected ?CacheStrategyInterface $cache = null;

    /**
     * @param MapperFactory $mapperFactory
     * @param CacheStrategyInterface|null $cache Driver de cache opcional para otimização de consultas.
     */
    public function __construct(MapperFactory $mapperFactory, ?CacheStrategyInterface $cache = null)
    {
        $this->mapperFactory = $mapperFactory;
        $this->cache = $cache;
    }

    /**
     * Define ou altera a estratégia de cache em tempo de execução.
     */
    public function setCache(CacheStrategyInterface $cache): void
    {
        $this->cache = $cache;
    }
}