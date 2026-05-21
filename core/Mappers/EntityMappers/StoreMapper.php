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
     * Obtém os dados de uma loja com base no seu hostname (URL ou SSL).
     */
    public function getStoreByHostname(string $hostname): ?array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("REPLACE(`url`, 'www.', '') = ?", [$hostname])
            ->select('*');

        $result = $this->dao->executeQuery($query);
        return $result[0] ?? null;
    }
}