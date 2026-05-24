<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\ApiIp;

/**
 * Mapper para a entidade ApiIp.
 * Isola a camada de banco de dados (tabela api_ip).
 */
class ApiIpMapper extends BaseMapper
{
    protected string $table = 'api_ip';
    protected string $entityClass = ApiIp::class;
}