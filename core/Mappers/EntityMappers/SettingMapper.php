<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Class SettingMapper
 * 
 * Gerencia as operações de banco de dados para as configurações da loja (Alpha Engine).
 */
class SettingMapper extends BaseMapper
{
    protected string $tableName = 'setting';

    /**
     * Busca configurações da loja padrão (0) e da loja atual, já ordenadas.
     */
    public function findByStoreId(int $storeId): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("store_id = 0 OR store_id = ?", [$storeId])
            ->orderBy('store_id', 'ASC');

        return $this->dao->executeQuery($query);
    }
}