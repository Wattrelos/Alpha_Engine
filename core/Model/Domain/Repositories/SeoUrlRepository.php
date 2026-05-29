<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\SeoUrlMapper;
use Alpha\Model\Domain\Entities\SeoUrl;

class SeoUrlRepository extends AbstractRepository
{
    public function getQueryByKeyword(string $keyword, int $storeId, int $languageId): string
    {
        $cacheKey = "seo_url.keyword.{$keyword}.store.{$storeId}.lang.{$languageId}";
        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = $this->mapperFactory->get(SeoUrlMapper::class)->search([
            'keyword' => $keyword,
            'store_id' => $storeId,
            'language_id' => $languageId
        ]);

        $queryString = '';
        if (!empty($results)) {
            /** @var SeoUrl $seoUrl */
            $seoUrl = $results[0];
            $queryString = $seoUrl->getKey() . '=' . $seoUrl->getValue();
        }

        if ($this->cache) {
            $this->cache->set($cacheKey, $queryString);
        }

        return $queryString;
    }

    public function getKeywordByQuery(string $key, string $value, int $storeId, int $languageId): string
    {
        $cacheKey = "seo_url.query.{$key}.{$value}.store.{$storeId}.lang.{$languageId}";
        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = $this->mapperFactory->get(SeoUrlMapper::class)->search([
            'key' => $key,
            'value' => $value,
            'store_id' => $storeId,
            'language_id' => $languageId
        ]);

        $keyword = '';
        if (!empty($results)) {
            /** @var SeoUrl $seoUrl */
            $keyword = $results[0]->getKeyword();
        }

        if ($this->cache) {
            $this->cache->set($cacheKey, $keyword);
        }

        return $keyword;
    }

    /**
     * Alpha Engine: Preload SEO URLs for a batch of values to avoid N+1 queries.
     *
     * @param array $values
     * @param string $key
     * @param int $storeId
     * @param int $languageId
     */
    public function primeCache(array $values, string $key, int $storeId, int $languageId): void
    {
        if (empty($values) || !$this->cache) {
            return;
        }

        $valuesToFetch = [];
        foreach ($values as $value) {
            $valueStr = (string)$value;
            $cacheKey = "seo_url.query.{$key}.{$valueStr}.store.{$storeId}.lang.{$languageId}";
            if (!$this->cache->has($cacheKey)) {
                $valuesToFetch[] = $valueStr;
            }
        }

        if (empty($valuesToFetch)) {
            return;
        }

        /** @var SeoUrlMapper $mapper */
        $mapper = $this->mapperFactory->get(SeoUrlMapper::class);
        $results = $mapper->getKeywordsByQueries($key, $valuesToFetch, $storeId, $languageId);

        $found = [];
        foreach ($results as $row) {
            $valStr = (string)$row['value'];
            $found[$valStr] = $row['keyword'];
        }

        foreach ($valuesToFetch as $valueStr) {
            $cacheKey = "seo_url.query.{$key}.{$valueStr}.store.{$storeId}.lang.{$languageId}";
            $keyword = $found[$valueStr] ?? '';
            $this->cache->set($cacheKey, $keyword);
        }
    }
}
