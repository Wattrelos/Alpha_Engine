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
    protected mixed $registry = null;
    protected ?CacheStrategyInterface $cache = null;
    protected string $mapperClass = ''; // Definido nas classes filhas para uso genérico do getIndexData

    /**
     * @param MapperFactory $mapperFactory
     * @param mixed $registry O Registry, Container ou nulo.
     * @param CacheStrategyInterface|null $cache Driver de cache opcional para otimização de consultas.
     */
    public function __construct(MapperFactory $mapperFactory, mixed $registry = null, ?CacheStrategyInterface $cache = null)
    {
        $this->mapperFactory = $mapperFactory;
        $this->registry = $registry;
        $this->cache = $cache;
    }

    /**
     * Define ou altera a estratégia de cache em tempo de execução.
     */
    public function setCache(CacheStrategyInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * Alpha Engine: Carrega traduções de forma nativa e segura.
     * 
     * @param string $route Rota do arquivo de idioma (ex: 'common/header')
     * @return array
     */
    protected function loadLanguage(string $route): array
    {
        if ($this->registry && method_exists($this->registry, 'get')) {
            $lang = $this->registry->get('language');
            return $lang ? $lang->load($route) : [];
        }
        return [];
    }

    /**
     * Alpha Engine: Carrega configurações de forma segura.
     */
    protected function loadConfig(string $filename): void
    {
        if ($this->registry && method_exists($this->registry, 'get')) {
            $factory = $this->registry->get('alpha_repository_factory');
            if ($factory) {
                $factory->get(ConfigurationRepository::class)->loadFile($filename);
            }
        }
    }

    /**
     * Permite acesso transparente aos serviços e shorthands comuns.
     */
    public function __get(string $key): mixed
    {
        if ($this->registry && method_exists($this->registry, 'get')) {
            if ($key === 'store_id') {
                $config = $this->registry->get('config');
                return $config ? (int)$config->get('config_store_id') : 0;
            }
            if ($key === 'language_id') {
                $config = $this->registry->get('config');
                return $config ? (int)$config->get('config_language_id') : 2;
            }
            return $this->registry->get($key);
        }
        return null;
    }

    /**
     * Implementação padrão para index. 
     * Pode ser sobrescrito nas classes filhas para lógicas complexas de paginação.
     */
    public function getIndexData(array $filters = []): array
    {
        if ($this->mapperClass) {
            return $this->mapperFactory->get($this->mapperClass)->findBy($filters);
        }
        return [];
    }
}
