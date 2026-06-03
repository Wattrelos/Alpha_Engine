<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Class StoreMapper
 * 
 * Gerencia as operações de banco de dados para a entidade Lojas (Store).
 */
class StoreMapper extends BaseMapper
{
    protected string $tableName = 'store';

    /**
     * Obtém os dados de uma loja específica pelo ID.
     */
    public function getStore(int $store_id): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`id` = ?", [$store_id])
            ->select('DISTINCT *, id AS store_id');

        $result = $this->dao->executeQuery($query);
        return $result[0] ?? [];
    }

    /**
     * Obtém os dados de uma loja com base no seu hostname (URL ou SSL).
     */
    public function getStoreByHostname(string $hostname): ?array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("REPLACE(`url`, 'www.', '') = ?", [$hostname])
            ->select('*, id AS store_id');

        $result = $this->dao->executeQuery($query);
        return $result[0] ?? null;
    }

    /**
     * Obtém todas as lojas cadastradas e implementa cache em memória.
     */
    public function getStores(): array
    {
        $cache_key = 'store.all';
        $store_data = $this->cache ? $this->cache->get($cache_key) : null;

        if (!$store_data) {
            $query = (new QueryBuilder())
                ->from($this->getFullTableName())
                ->orderBy("`url`", "ASC")
                ->select('*, id AS store_id');

            $store_data = $this->dao->executeQuery($query);
            if ($this->cache) $this->cache->set($cache_key, $store_data);
        }

        return $store_data;
    }
}