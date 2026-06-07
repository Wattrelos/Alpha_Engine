<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\OrderStatus;

class OrderStatusMapper extends BaseMapper
{
    protected string $tableName = 'order_status';
    protected string $entityClass = OrderStatus::class;

    public function getOrderStatuses(int $languageId): array
    {
        $builder = (new QueryBuilder())
            ->from($this->getFullTableName(), 'os')
            ->where('os.language_id = ?', [(int)$languageId])
            ->orderBy('os.name', 'ASC');

        $rows = $this->dao->executeQuery($builder);
        $entities = [];
        foreach ($rows as $row) {
            $entities[] = $this->dao->hydrate($this->entityClass, $row);
        }
        return $entities;
    }

    public function getOrderStatus(int $orderStatusId, int $languageId): ?OrderStatus
    {
        $builder = (new QueryBuilder())
            ->from($this->getFullTableName(), 'os')
            ->where('os.id = ?', [(int)$orderStatusId])
            ->where('os.language_id = ?', [(int)$languageId])
            ->limit(1);

        $results = $this->dao->executeQuery($builder);
        return $results ? $this->dao->hydrate($this->entityClass, $results[0]) : null;
    }
}
