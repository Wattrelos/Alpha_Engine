<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\SeoUrlMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * Class SeoUrlRepository
 * 
 * Camada de domínio para a gestão de URLs amigáveis (SEO).
 * Implementa primeCache para carregamento em lote e utiliza CacheStrategy
 * para aliviar o banco de dados em páginas com muitos links.
 */
class SeoUrlRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Cache estático em memória para a requisição atual (Identity Map local).
     */
    private array $urlCache = [];

    protected function getMapper(): SeoUrlMapper
    {
        return $this->mapperFactory->get(SeoUrlMapper::class);
    }

    /**
     * Carrega em lote as URLs amigáveis para uma lista de queries.
     * Previne o problema de N+1 queries na geração de listagens de produtos e categorias.
     */
    public function primeCache(array $queries, int $storeId, int $languageId): void
    {
        if (empty($queries)) {
            return;
        }

        $uncachedQueries = [];
        $cacheKeyBase = "seo_url_{$storeId}_{$languageId}_";

        // 1. Verifica memória local e Cache Físico primeiro
        foreach ($queries as $query) {
            if (isset($this->urlCache[$query])) {
                continue;
            }

            $cacheKey = $cacheKeyBase . md5($query);
            if ($this->cache !== null && $this->cache->has($cacheKey)) {
                $this->urlCache[$query] = $this->cache->get($cacheKey);
            } else {
                $uncachedQueries[] = $query;
            }
        }

        if (empty($uncachedQueries)) {
            return;
        }

        // 2. Busca no banco de dados via Mapper apenas o que não estava em cache
        $results = $this->getMapper()->getUrlsByQueries($uncachedQueries, $storeId, $languageId);

        // 3. Popula a memória e o Cache Físico
        foreach ($uncachedQueries as $query) {
            $keyword = $results[$query] ?? ''; // Fallback para vazio se não existir
            $this->urlCache[$query] = $keyword;

            if ($this->cache !== null) {
                $cacheKey = $cacheKeyBase . md5($query);
                // Salva por tempo longo, URLs amigáveis não mudam com frequência (24h)
                $this->cache->set($cacheKey, $keyword, 86400);
            }
        }
    }

    /**
     * Resolve uma query interna para a URL amigável (slug).
     */
    public function getKeywordByQuery(string $query, int $storeId, int $languageId): string
    {
        // Se já está no primeCache, retorna direto
        if (isset($this->urlCache[$query])) {
            return $this->urlCache[$query];
        }

        $cacheKey = "seo_url_{$storeId}_{$languageId}_" . md5($query);

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            $keyword = $this->cache->get($cacheKey);
            $this->urlCache[$query] = $keyword;
            return $keyword;
        }

        $keyword = $this->getMapper()->getKeywordByQuery($query, $storeId, $languageId);
        
        $this->urlCache[$query] = $keyword;
        
        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $keyword, 86400);
        }

        return $keyword;
    }

    /**
     * Resolve um slug da URL para a query interna do sistema.
     */
    public function getQueryByKeyword(string $keyword, int $storeId, int $languageId): string
    {
        $cacheKey = "seo_query_{$storeId}_{$languageId}_" . md5($keyword);

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $query = $this->getMapper()->getQueryByKeyword($keyword, $storeId, $languageId);

        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $query, 86400);
        }

        return $query;
    }

    // Métodos obrigatórios da Interface BaseRepository
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}