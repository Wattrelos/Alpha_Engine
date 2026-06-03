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
    public function getTheme(string $route, string $theme): ?array
    {
        // Alpha Engine: Pega o Store ID de forma dinâmica pelo contexto global da loja
        $storeId = $this->container ? (int)$this->container->get('config')->get('config_store_id') : 0;

        $query = (new QueryBuilder())
            ->select('*')
            ->from($this->getFullTableName())
            ->where('store_id = ?', [$storeId])
            ->where('route = ?', [$route])
            ->where('status = ?', [1]);

        $result = $this->dao->executeQuery($query);

        return $result[0] ?? null;
    }
}