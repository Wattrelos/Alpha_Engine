<?php
namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\SeoUrl;
use Alpha\Model\DataAccessObject\QueryBuilder;

class SeoUrlMapper extends BaseMapper
{
    protected string $tableName = 'seo_url';
    protected string $entityClass = SeoUrl::class;

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