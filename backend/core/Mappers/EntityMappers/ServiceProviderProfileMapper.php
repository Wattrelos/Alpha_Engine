<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Quotation\ServiceProviderProfile;

class ServiceProviderProfileMapper extends BaseMapper
{
    protected string $tableName = 'service_provider_profile';
    protected string $entityClass = ServiceProviderProfile::class;
}
