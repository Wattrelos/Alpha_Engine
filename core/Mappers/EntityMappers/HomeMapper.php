<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

class HomeMapper extends BaseMapper
{
    protected string $tableName = 'setting';
    protected string $primaryKey = 'id';

    /**
     * Recupera os metadados de SEO da página inicial (Alpha Engine).
     */
    public function getHomeMetadata(int $storeId): array
    {
        $keys = ['config_meta_title', 'config_meta_description', 'config_meta_keyword'];
        
        $builder = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("store_id = ?", [$storeId])
            ->where("`key` IN (" . implode(',', array_fill(0, count($keys), '?')) . ")", $keys)
            ->select('`key`', '`value`');

        $results = $this->dao->executeQuery($builder);
        
        $metadata = [];
        foreach ($results as $result) {
            $metadata[$result['key']] = $result['value'];
        }
        
        return $metadata;
    }
}