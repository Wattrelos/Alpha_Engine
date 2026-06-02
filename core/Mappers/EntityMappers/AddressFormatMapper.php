<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\AddressFormat;

/**
 * AddressFormatMapper - Gerencia a persistência da entidade AddressFormat (Alpha Engine).
 */
class AddressFormatMapper extends BaseMapper
{
    protected string $tableName = 'address_format';
    protected string $entityClass = AddressFormat::class;
}