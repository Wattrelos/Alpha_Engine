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

    public function findAll(): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->select('*');

        return $this->dao->executeQuery($query);
    }
}