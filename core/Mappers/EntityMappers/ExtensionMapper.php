<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Opencart\System\Engine\Registry;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Extension;
use Opencart\System\Library\Cache;

/**
 * ExtensionMapper - Gerencia a persistência e consulta de extensões instaladas.
 */
class ExtensionMapper extends BaseMapper
{
    protected string $entityClass = Extension::class;
    protected string $tableName = 'extension';

    protected Cache $cache;

    public function __construct(Registry $registry = null) // Adiciona valor padrão para compatibilidade
    {
        parent::__construct($registry);

        // Alpha Engine: Resolução de dependência do serviço de Cache via Registry global
        $this->cache = $this->registry ? $this->registry->get('cache') : null;
    }

    /**
     * Alpha Engine: Recupera entidades Extension filtradas por tipo.
     * 
     * @param string $type Tipo da extensão (analytics, theme, shipping, etc).
     * @return Extension[]
     */
    public function getExtensionsByType(string $type): array
    {
        $cache_key = 'extension.' . $type;
        
        // Alpha Engine: Tenta recuperar a coleção do cache para evitar queries repetitivas
        $extensions = $this->cache ? $this->cache->get($cache_key) : null;

        if ($extensions !== null) {
            return $extensions;
        }

        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`type` = ?", [$type])
            ->select('id');

        $results = $this->dao->executeQuery($query);
        $ids = array_map('intval', array_column($results, 'id'));

        $extensions = !empty($ids) ? $this->dao->readByIds($this->entityClass, $ids) : [];

        // Alpha Engine: Armazena o resultado no cache
        if ($this->cache) {
            $this->cache->set($cache_key, $extensions);
        }

        return $extensions;
    }

    /**
     * Alpha Engine (Legacy Bridge): Recupera todas as extensões.
     * 
     * Essencial para compatibilidade com módulos da comunidade que chamam
     * $this->model_setting_extension->getExtensions().
     * Retorna array bruto em vez de Entidades para evitar quebra de Array Access.
     * 
     * @return array
     */
    public function getExtensions(): array
    {
        $cache_key = 'extension.all';
        $extensions = $this->cache ? $this->cache->get($cache_key) : null;

        if ($extensions !== null) {
            return $extensions;
        }

        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->select('*');

        $results = $this->dao->executeQuery($query);

        if ($this->cache) {
            $this->cache->set($cache_key, $results);
        }

        return $results;
    }

    /**
     * Alpha Engine: Recupera nomes de extensões distintas.
     * Mantém a compatibilidade de listagem legada.
     */
    public function getDistinctExtensions(): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->select('DISTINCT `extension`');

        return $this->dao->executeQuery($query);
    }

    /**
     * Alpha Engine: Recupera uma extensão específica pelo tipo e código.
     */
    public function getExtensionByCode(string $type, string $code): ?Extension
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`type` = ?", [$type])
            ->where("`code` = ?", [$code])
            ->select('id');

        $results = $this->dao->executeQuery($query);
        
        return $results ? $this->findById((int)$results[0]['id']) : null;
    }

    public function findById(int $id): ?Extension
    {
        $extension = new Extension();
        $extension->setId($id);
        $results = $this->dao->read($extension);
        return $results ? $results[0] : null;
    }
}