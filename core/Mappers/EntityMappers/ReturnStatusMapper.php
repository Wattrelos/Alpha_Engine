<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ReturnStatus;

/**
 * Mapper para a entidade ReturnStatus.
 * Mapeia o dicionário de status que uma devolução pode assumir.
 */
class ReturnStatusMapper extends BaseMapper
{
    protected string $table = 'return_status';
    protected string $entityClass = ReturnStatus::class;
}