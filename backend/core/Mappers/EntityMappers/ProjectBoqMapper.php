<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoq;

class ProjectBoqMapper extends BaseMapper
{
    protected string $tableName = 'project_boq';
    protected string $entityClass = ProjectBoq::class;
}
