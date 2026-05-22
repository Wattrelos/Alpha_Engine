<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * StartupMapper - Gerencia as operações de banco de dados para as rotinas de inicialização.
 */
class StartupMapper extends BaseMapper
{
    protected string $tableName = 'startup';

    public function getStartups(): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`status` = ?", ['1'])
            ->orderBy("`sort_order`", "ASC")
            ->select('*');

        return $this->dao->executeQuery($query);
    }
}