<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\SeoUrlMapper;
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
    public function primeCache(array $values, string $key, int $storeId, int $languageId): void
    {
        if (empty($values)) {
            return;
        }

        $uncachedValues = [];
        $cacheKeyBase = "seo_url_{$storeId}_{$languageId}_{$key}_";

        // 1. Verifica memória local e Cache Físico primeiro
        foreach ($values as $value) {
            $cacheHash = $key . '=' . $value;
            
            if (isset($this->urlCache[$cacheHash])) {
                continue;
            }

            $cacheKey = $cacheKeyBase . md5($value);
            if ($this->cache !== null && $this->cache->has($cacheKey)) {
                $this->urlCache[$cacheHash] = $this->cache->get($cacheKey);
            } else {
                $uncachedValues[] = $value;
            }
        }

        if (empty($uncachedValues)) {
            return;
        }

        // 2. Busca no banco de dados via Mapper apenas o que não estava em cache
        $results = [];
        if (method_exists($this->getMapper(), 'getUrlsByValues')) {
            // O Mapper deve retornar um array mapeando [$value => $keyword]
            $res = $this->getMapper()->getUrlsByValues($uncachedValues, $key, $storeId, $languageId);
            if (is_array($res)) {
                $results = $res;
            }
        }

        // 3. Popula a memória e o Cache Físico
        foreach ($uncachedValues as $value) {
            // A MÁGICA ACONTECE AQUI: Guarda a string vazia para evitar N+1
            $keyword = $results[$value] ?? ''; 
            $cacheHash = $key . '=' . $value;
            $this->urlCache[$cacheHash] = $keyword;

            if ($this->cache !== null) {
                $cacheKey = $cacheKeyBase . md5($value);
                // Salva por tempo longo, URLs amigáveis não mudam com frequência (24h)
                $this->cache->set($cacheKey, $keyword, 86400);
            }
        }
    }

    /**
     * Resolve uma query interna para a URL amigável (slug).
     */
    public function getKeywordByQuery(string $key, string $value, int $storeId, int $languageId): string
    {
        $cacheHash = $key . '=' . $value;
        
        // Se já está no primeCache, retorna direto
        if (isset($this->urlCache[$cacheHash])) {
            return $this->urlCache[$cacheHash];
        }

        $cacheKey = "seo_url_{$storeId}_{$languageId}_{$key}_" . md5($value);

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            $keyword = $this->cache->get($cacheKey);
            $this->urlCache[$cacheHash] = $keyword;
            return $keyword;
        }

        $keyword = $this->getMapper()->getKeywordByQuery($key, $value, $storeId, $languageId);
        
        $this->urlCache[$cacheHash] = $keyword;
        
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
        $cacheHash = 'reverse_' . $keyword;
        
        if (isset($this->urlCache[$cacheHash])) {
            return $this->urlCache[$cacheHash];
        }

        $cacheKey = "seo_query_{$storeId}_{$languageId}_" . md5($keyword);

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            $query = $this->cache->get($cacheKey);
            $this->urlCache[$cacheHash] = $query;
            return $query;
        }

        $query = $this->getMapper()->getQueryByKeyword($keyword, $storeId, $languageId);
        $this->urlCache[$cacheHash] = $query;

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