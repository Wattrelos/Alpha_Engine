<?php
namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Startup;

class StartupMapper extends BaseMapper
{
    protected string $tableName = 'startup';
    protected string $entityClass = Startup::class;
}