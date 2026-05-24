<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\OrderReturn;

/**
 * Mapper para a entidade OrderReturn.
 * Isola o banco de dados (tabela return) da lógica de domínio.
 */
class OrderReturnMapper extends BaseMapper
{
    protected string $table = 'return';
    protected string $entityClass = OrderReturn::class;
}