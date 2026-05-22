<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * EventMapper - Gerencia as operações de banco de dados para a entidade Eventos.
 */
class EventMapper extends BaseMapper
{
    protected string $tableName = 'event';

    public function getEvents(): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`status` = ?", ['1'])
            ->orderBy("`sort_order`", "ASC")
            ->select('*');

        return $this->dao->executeQuery($query);
    }
}