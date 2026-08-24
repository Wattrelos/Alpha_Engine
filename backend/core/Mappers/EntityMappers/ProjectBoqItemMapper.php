<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoqItem;

class ProjectBoqItemMapper extends BaseMapper
{
    protected string $tableName = 'project_boq_item';
    protected string $entityClass = ProjectBoqItem::class;
}
