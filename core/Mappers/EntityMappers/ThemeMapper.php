<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Class ThemeMapper
 * 
 * Gerencia a consulta ao banco de dados para resoluções de temas e templates.
 */
class ThemeMapper extends BaseMapper
{
    protected string $tableName = 'theme';

    /**
     * Busca as informações de tema baseadas na rota e na loja ativa.
     */
    public function getTheme(string $route, int $storeId): ?array
    {
        $query = (new QueryBuilder())
            ->select('*')
            ->from($this->getFullTableName())
            ->where('store_id', '=', $storeId)
            ->where('route', '=', $route)
            ->where('status', '=', 1);

        $result = $this->dao->executeQuery($query);

        return $result[0] ?? null;
    }
}