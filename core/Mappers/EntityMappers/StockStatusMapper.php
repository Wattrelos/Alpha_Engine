<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\StockStatus;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * StockStatusMapper - Gerencia a recuperação das mensagens de status de estoque (Alpha Engine).
 */
class StockStatusMapper extends BaseMapper {

    protected string $tableName = 'stock_status';
    protected string $entityClass = StockStatus::class;

    /**
     * Obtém as informações de status de estoque pelo ID e idioma atual da loja.
     *
     * @param int $stock_status_id
     * @return array|null
     */
    public function getStockStatus(int $stock_status_id): ?array {
        $language_id = (int)$this->container->get('config')->get('config_language_id');
        
        $query = (new QueryBuilder())
            ->select('*')
            ->from($this->getFullTableName())
            ->where("id = ?", [$stock_status_id])
            ->where("language_id = ?", [$language_id])
            ->limit(1);

        $results = $this->dao->executeQuery($query);
        return $results[0] ?? null;
    }
}
