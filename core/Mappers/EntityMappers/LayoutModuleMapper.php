<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * LayoutModuleMapper
 * 
 * Gerencia a persistência e extração de blocos/módulos atrelados a um Layout.
 */
class LayoutModuleMapper extends BaseMapper
{
    protected string $tableName = 'layout_module';

    /**
     * Busca todos os módulos vinculados a um Layout específico.
     * Os resultados já vêm ordenados pela engine de banco de dados por Posição e Ordem.
     */
    public function findModulesByLayoutId(int $layoutId): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`layout_id` = ?", [$layoutId])
            ->orderBy("`position`", "ASC")
            ->orderBy("`sort_order`", "ASC")
            ->select('*');
            
        return $this->dao->executeQuery($query);
    }
}