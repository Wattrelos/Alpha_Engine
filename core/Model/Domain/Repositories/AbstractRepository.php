<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\MapperFactory;
use Alpha\Support\Cache\CacheStrategyInterface;
use Opencart\System\Engine\Registry;

/**
 * AbstractRepository - Classe base para todos os Repositórios da Alpha Engine.
 * 
 * Fornece a infraestrutura necessária para a camada de domínio, incluindo
 * o acesso à fábrica de mappers e o suporte opcional à estratégia de cache.
 */
abstract class AbstractRepository
{
    protected MapperFactory $mapperFactory;
    protected Registry $registry;
    protected ?CacheStrategyInterface $cache = null;

    /**
     * @param MapperFactory $mapperFactory
     * @param Registry $registry
     * @param CacheStrategyInterface|null $cache Driver de cache opcional para otimização de consultas.
     */
    public function __construct(MapperFactory $mapperFactory, Registry $registry, ?CacheStrategyInterface $cache = null)
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
     * Alpha Engine: Carrega traduções diretamente através do objeto Language nativo,
     * eliminando a dependência do Loader legado ($this->load->language).
     * 
     * @param string $route Rota do arquivo de idioma (ex: 'common/header')
     * @return array
     */
    protected function loadLanguage(string $route): array
    {
        return $this->registry->get('language')->load($route) ?: [];
    }
    /**
     * Alpha Engine: Substituto direto para $this->load->config()
     */
    protected function loadConfig(string $filename): void
    {
        $factory = $this->registry->get('alpha_repository_factory');
        $factory->get(ConfigurationRepository::class)->loadFile($filename);
    }


    /**
     * Permite acesso transparente aos serviços do OpenCart e shorthands comuns.
     */
    public function __get(string $key): mixed
    {
        if ($key === 'store_id') {
            return (int)$this->registry->get('config')->get('config_store_id');
        }
        if ($key === 'language_id') {
            return (int)$this->registry->get('config')->get('config_language_id');
        }
        return $this->registry->get($key);
    }
}
