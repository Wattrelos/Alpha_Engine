<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\StartupMapper;
use Alpha\Model\Domain\Entities\Startup;

class StartupRepository extends AbstractRepository
{
    /**
     * Retorna todos os Startups ativos ordenados corretamente.
     * @return Startup[]
     */
    public function getStartups(): array
    {
        $cacheKey = 'startup.all.active';
        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $startups = $this->mapperFactory->get(StartupMapper::class)->search(['status' => 1], ['sort_order' => 'ASC']);

        if ($this->cache) {
            $this->cache->set($cacheKey, $startups);
        }

        return $startups;
    }
}