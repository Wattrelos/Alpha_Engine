<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Customer\CustomerAddresses;

class CustomerAddressesMapper extends BaseMapper
{
    protected string $tableName = 'customer_addresses';
    protected string $entityClass = CustomerAddresses::class;
}
