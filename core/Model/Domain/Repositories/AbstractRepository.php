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
    protected ?\Containers\AppContainer $container = null;
    protected ?CacheStrategyInterface $cache = null;
    protected string $mapperClass = ''; // Definido nas classes filhas para uso genérico do getIndexData

    /**
     * @param MapperFactory $mapperFactory
     * @param \Containers\AppContainer|null $container O Container de Dependências (PSR-11)
     * @param CacheStrategyInterface|null $cache Driver de cache opcional para otimização de consultas.
     */
    public function __construct(MapperFactory $mapperFactory, ?\Containers\AppContainer $container = null, ?CacheStrategyInterface $cache = null)
    {
        $this->mapperFactory = $mapperFactory;
        $this->container = $container;
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
        if ($this->container && $this->container->has('language')) {
            $lang = $this->container->get('language');
            return $lang ? $lang->load($route) : [];
        }
        return [];
    }

    /**
     * Alpha Engine: Carrega configurações de forma segura.
     */
    protected function loadConfig(string $filename): void
    {
        if ($this->container && $this->container->has('alpha_repository_factory')) {
            $factory = $this->container->get('alpha_repository_factory');
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
        if ($this->container) {
            if ($key === 'store_id') {
                if ($this->container->has('configSettings')) {
                    $settings = $this->container->get('configSettings');
                    return (int)($settings['config_store_id'] ?? 0);
                }
                return 0;
            }
            if ($key === 'language_id') {
                return $this->container->has('languageId') ? (int)$this->container->get('languageId') : 2;
            }
            if ($this->container->has($key)) {
                return $this->container->get($key);
            }
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
