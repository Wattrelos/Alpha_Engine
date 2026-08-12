<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ReturnReason;

/**
 * Mapper para a entidade ReturnReason.
 * Mapeia os motivos de devolução reportados pelos clientes.
 */
class ReturnReasonMapper extends BaseMapper
{
    protected string $table = 'return_reason';
    protected string $entityClass = ReturnReason::class;
}