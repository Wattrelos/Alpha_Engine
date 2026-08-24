<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Quotation\ProjectRfq;

class ProjectRfqMapper extends BaseMapper
{
    protected string $tableName = 'project_rfq';
    protected string $entityClass = ProjectRfq::class;
}
