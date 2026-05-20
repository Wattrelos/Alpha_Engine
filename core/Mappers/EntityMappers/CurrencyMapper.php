<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Currency;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * CurrencyMapper - Gerencia a persistência e recuperação de moedas (Alpha Engine).
 */
class CurrencyMapper extends BaseMapper {

    protected string $entityClass = Currency::class;
    protected string $tableName = 'currency';

    /**
     * Recupera todas as moedas ativas.
     * 
     * @return array
     */
    public function getCurrencies(): array {
        $builder = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("status = ?", [1])
            ->orderBy("title", "ASC")
            ->select('id'); // Seleciona apenas o ID para hidratar as entidades completas

        $rows = $this->dao->executeQuery($builder);
        $ids = array_column($rows, 'id');

        return $this->dao->readByIds($this->entityClass, $ids); // Retorna entidades Currency
    }

    /**
     * Recupera uma moeda pelo seu código ISO (ex: 'BRL').
     * 
     * @param string $code
     * @return Currency|null
     */
    public function getCurrencyByCode(string $code): ?Currency {
        $builder = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("code = ?", [$code])
            ->select('id'); // Seleciona apenas o ID

        $rows = $this->dao->executeQuery($builder);
        
        if (!$rows) {
            return null;
        }

        $currency = $this->dao->readByIds($this->entityClass, [(int)$rows[0]['id']]); // Hidrata a entidade
        return $currency ? $currency[0] : null;
    }
}