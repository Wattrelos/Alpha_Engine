<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Currency;

/**
 * Class CurrencyMapper
 * 
 * Implementa o padrão DataAccessObject/Mapper para as Moedas,
 * centralizando consultas e garantindo reuso e performance (Alpha Engine).
 */
class CurrencyMapper extends BaseMapper
{
    protected string $tableName = 'currency';
    protected string $entityClass = Currency::class;

    /**
     * Recupera todas as moedas ativas na loja em formato Array (DTO legado).
     * 
     * @return array<int, array<string, mixed>>
     */
    public function findAllActive(): array
    {
        // O cache de memória estático foi removido, pois o Identity Map 
        // e o Repository Pattern já garantem a otimização das chamadas O(1).
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`status` = ?", ['1'])
            ->select('*');
            
        return $this->dao->executeQuery($query);
    }
}