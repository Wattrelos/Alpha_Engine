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
     * Busca configurações da loja ativa.
     * Alpha Engine: store_id = 1 foi eliminado via migração de dados.
     * Todos os registros de configuração pertencem a uma loja real (>= 1).
     */
    public function findByStoreId(int $storeId): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("store_id = ?", [$storeId])
            ->orderBy('store_id', 'ASC');

        return $this->dao->executeQuery($query);
    }
}
