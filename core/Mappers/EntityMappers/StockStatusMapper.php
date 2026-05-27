<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\StockStatus;

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
        $language_id = (int)$this->registry->get('config')->get('config_language_id');
        $sql = "SELECT * FROM `" . $this->getFullTableName() . "` WHERE `id` = :stock_status_id AND `language_id` = :language_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'stock_status_id' => $stock_status_id,
            'language_id'     => $language_id
        ]);
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
