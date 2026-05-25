<?php
namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\SeoUrl;
use Alpha\Model\DataAccessObject\QueryBuilder;

class SeoUrlMapper extends BaseMapper
{
    protected string $tableName = 'seo_url';
    protected string $entityClass = SeoUrl::class;

    private array $cache = [];

    /**
     * Prime Cache: Carrega URLs amigáveis em lote para a memória (Prevenção de N+1 Queries).
     */
    public function primeCache(array $ids, string $key, int $storeId, int $languageId): void
    {
        if (empty($ids)) {
            return;
        }

        $missingIds = [];
        foreach ($ids as $id) {
            $cacheKey = "{$key}_{$id}_{$storeId}_{$languageId}";
            if (!isset($this->cache[$cacheKey])) {
                $missingIds[] = $id;
                // Preenche provisoriamente para evitar novas queries repetitivas caso a URL não exista
                $this->cache[$cacheKey] = '';
            }
        }

        if (!empty($missingIds)) {
            $results = $this->getKeywordsByQueries($key, $missingIds, $storeId, $languageId);
            foreach ($results as $row) {
                $cacheKey = "{$key}_{$row['value']}_{$storeId}_{$languageId}";
                $this->cache[$cacheKey] = $row['keyword'];
            }
        }
    }

    /**
     * Recupera uma keyword do cache em memória ou realiza o fallback de consulta no banco.
     */
    public function getKeywordByQuery(string $key, string $value, int $storeId, int $languageId): string
    {
        $cacheKey = "{$key}_{$value}_{$storeId}_{$languageId}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $this->primeCache([$value], $key, $storeId, $languageId);
        return $this->cache[$cacheKey] ?? '';
    }

    public function getKeywordsByQueries(string $key, array $values, int $storeId, int $languageId): array
    {
        if (empty($values)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $params = array_merge([$key, $storeId, $languageId], $values);

        $query = (new QueryBuilder())
            ->from(DB_PREFIX . $this->tableName)
            ->where("`key` = ?", [$key])
            ->where("`store_id` = ?", [$storeId])
            ->where("`language_id` = ?", [$languageId])
            ->where("`value` IN ($placeholders)", $values)
            ->select('*');

        return $this->dao->executeQuery($query);
    }
}