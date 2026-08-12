<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ThemeMapper;

/**
 * Class ThemeRepository
 * 
 * Abstrai a consulta de temas provendo Identity Map (Cache) para evitar chamadas 
 * redundantes ao banco no carregamento de partes fracionadas do layout de frontend.
 */
class ThemeRepository extends AbstractRepository
{
    private array $themeCache = [];

    protected function getMapper(): ThemeMapper
    {
        return $this->mapperFactory->get(ThemeMapper::class);
    }

    /**
     * Retorna a configuração do tema baseada na rota da loja.
     */
    public function getTheme(string $route, string $theme): ?array
    {
        $cacheKey = $theme . '_' . $route;

        if (!array_key_exists($cacheKey, $this->themeCache)) {
            $this->themeCache[$cacheKey] = $this->getMapper()->getTheme($route, $theme, $this->getStoreId());
        }

        return $this->themeCache[$cacheKey];
    }
}
