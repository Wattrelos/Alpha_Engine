<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ReturnAction;

/**
 * Mapper para a entidade ReturnAction.
 * Mapeia as ações possíveis para uma devolução.
 */
class ReturnActionMapper extends BaseMapper
{
    protected string $table = 'return_action';
    protected string $entityClass = ReturnAction::class;
}