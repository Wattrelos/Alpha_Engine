<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Quotation\ProjectBid;

class ProjectBidMapper extends BaseMapper
{
    protected string $tableName = 'project_bid';
    protected string $entityClass = ProjectBid::class;
}
