<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Class CurrencyMapper
 * 
 * Implementa o padrão DataAccessObject/Mapper para as Moedas,
 * centralizando consultas e garantindo reuso e performance (Alpha Engine).
 */
class CurrencyMapper extends BaseMapper
{
    protected string $tableName = 'currency';

    /**
     * Recupera todas as moedas ativas na loja.
     * 
     * @return array<int, array<string, mixed>>
     */
    public function findAllActive(): array
    {
        // Alpha Engine: Memoization Cache (Evita consultas duplicadas na mesma requisição)
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        // Substitui a chamada direta e sem filtro do OpenCart original.
        // Adicionado filtro de status para garantir performance e integridade de negócio.
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`status` = ?", ['1'])
            ->select('*');
            
        $cache = $this->dao->executeQuery($query);
        return $cache;
    }
}