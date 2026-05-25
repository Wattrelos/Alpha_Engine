<?php
namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Event;

class EventMapper extends BaseMapper
{
    protected string $tableName = 'event';
    protected string $entityClass = Event::class;
}