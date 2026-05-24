<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ReturnHistory;

/**
 * Mapper para a entidade ReturnHistory.
 * Mapeia o registro de interações e mudanças de status.
 */
class ReturnHistoryMapper extends BaseMapper
{
    protected string $table = 'return_history';
    protected string $entityClass = ReturnHistory::class;
}