<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\CustomerTransaction;

/**
 * Mapper para a entidade CustomerTransaction.
 * Isola a camada de banco de dados (tabela customer_transaction).
 */
class CustomerTransactionMapper extends BaseMapper
{
    protected string $table = 'customer_transaction';
    protected string $entityClass = CustomerTransaction::class;
}