<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\BannerMapper;

/**
 * Class BannerRepository
 * 
 * Abstrai a consulta de banners provendo Identity Map (Cache) para evitar chamadas 
 * redundantes ao banco no carregamento dos mesmos banners em áreas diferentes do layout.
 */
class BannerRepository extends AbstractRepository
{
    private array $identityMap = [];

    protected function getMapper(): BannerMapper
    {
        return $this->mapperFactory->get(BannerMapper::class);
    }

    public function getBanner(int $bannerId): array
    {
        if (!array_key_exists($bannerId, $this->identityMap)) {
            $this->identityMap[$bannerId] = $this->getMapper()->getBanner($bannerId, $this->language_id);
        }

        return $this->identityMap[$bannerId];
    }
}
