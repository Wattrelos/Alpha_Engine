<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\SeoUrl;

/**
 * SeoUrlMapper - Gerencia a resolução de URLs amigáveis com cache de alta performance.
 * 
 * Melhoras Alpha Engine:
 * - Cache Estático de Lookup: Mapeia rotas para slugs e vice-versa em memória.
 * - Integração com DAO: Utiliza o Identity Map para evitar recriação de objetos.
 * - Busca Contextualizada: Considera Store e Language nativamente.
 */
class SeoUrlMapper extends BaseMapper {
    protected string $entityClass = SeoUrl::class;
    protected string $tableName = 'seo_url';
    
    // Cache de lookup para evitar queries repetitivas na mesma requisição
    private static array $keywordCache = []; // [store_id][language_id][key][value] => keyword
    private static array $queryCache = [];   // [store_id][language_id][keyword] => SeoUrl

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Encontra a Keyword (Slug) para uma rota específica.
     */
    public function getKeywordByQuery(string $key, string $value, int $store_id, int $language_id): string {
        if (isset(self::$keywordCache[$store_id][$language_id][$key][$value])) {
            return self::$keywordCache[$store_id][$language_id][$key][$value];
        }

        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'seo_url')
            ->where("`key` = ?", [$key])
            ->where("`value` = ?", [$value])
            ->where("store_id = ?", [$store_id])
            ->where("language_id = ?", [$language_id])
            ->select('keyword');

        $results = $this->dao->executeQuery($query);
        $keyword = $results ? $results[0]['keyword'] : '';

        self::$keywordCache[$store_id][$language_id][$key][$value] = $keyword;
        
        return $keyword;
    }

    /**
     * Encontra a Entidade SeoUrl para um slug (keyword) específico.
     */
    public function getSeoUrlByKeyword(string $keyword, int $store_id, int $language_id): ?SeoUrl {
        if (isset(self::$queryCache[$store_id][$language_id][$keyword])) {
            return self::$queryCache[$store_id][$language_id][$keyword];
        }

        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'seo_url')
            ->where("keyword = ?", [$keyword])
            ->where("store_id = ?", [$store_id])
            ->where("language_id = ?", [$language_id])
            ->select('id');

        $results = $this->dao->executeQuery($query);
        
        if (!$results) {
            return null;
        }

        $seoUrl = new SeoUrl();
        $seoUrl->setId((int)$results[0]['id']);
        
        // O DAO.read preencherá o objeto e usará o Identity Map automaticamente
        $hydrated = $this->dao->read($seoUrl);
        $result = $hydrated ? $hydrated[0] : null;

        if ($result) {
            self::$queryCache[$store_id][$language_id][$keyword] = $result;
        }

        return $result;
    }

    /**
     * Carrega em lote as URLs para evitar N+1 em listagens.
     */
    public function primeCache(array $values, string $key, int $store_id, int $language_id): void {
        $toFetch = [];
        foreach ($values as $v) {
            if (!isset(self::$keywordCache[$store_id][$language_id][$key][$v])) {
                $toFetch[] = $v;
            }
        }

        if (empty($toFetch)) return;

        $placeholders = implode(',', array_fill(0, count($toFetch), '?'));
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'seo_url')
            ->where("`key` = ?", [$key])
            ->where("`value` IN ($placeholders)", $toFetch)
            ->where("store_id = ?", [$store_id])
            ->where("language_id = ?", [$language_id])
            ->select('value', 'keyword');

        $results = $this->dao->executeQuery($query);
        foreach ($results as $row) {
            self::$keywordCache[$store_id][$language_id][$key][$row['value']] = $row['keyword'];
        }
    }
}