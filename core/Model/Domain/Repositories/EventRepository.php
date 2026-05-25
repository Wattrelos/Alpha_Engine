<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\EventMapper;
use Alpha\Model\Domain\Entities\Event;

class EventRepository extends AbstractRepository
{
    /**
     * Retorna todos os Eventos ativos ordenados corretamente.
     * @return Event[]
     */
    public function getEvents(): array
    {
        $cacheKey = 'event.all.active';
        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $events = $this->mapperFactory->get(EventMapper::class)->search(['status' => 1], ['sort_order' => 'ASC']);

        if ($this->cache) {
            $this->cache->set($cacheKey, $events);
        }

        return $events;
    }
}