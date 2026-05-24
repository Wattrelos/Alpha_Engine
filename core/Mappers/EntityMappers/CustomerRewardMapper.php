<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\CustomerReward;

/**
 * Mapper para a entidade CustomerReward.
 * Isola a camada de banco de dados (tabela customer_reward).
 */
class CustomerRewardMapper extends BaseMapper
{
    protected string $table = 'customer_reward';
    protected string $entityClass = CustomerReward::class;
}