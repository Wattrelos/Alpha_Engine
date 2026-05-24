<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ApiHistory;

/**
 * Mapper para a entidade ApiHistory.
 * Isola a camada de banco de dados (tabela api_history).
 */
class ApiHistoryMapper extends BaseMapper
{
    protected string $table = 'api_history';
    protected string $entityClass = ApiHistory::class;
}