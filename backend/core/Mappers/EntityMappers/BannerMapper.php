<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar banners e suas imagens.
 */
class BannerMapper extends BaseMapper {
    protected string $tableName = 'banner';

    /**
     * Obtém as imagens associadas a um banner, filtradas por idioma e status ativo.
     * 
     * @param int $banner_id
     * @param int $language_id
     * @return array
     */
    public function getBanner(int $banner_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName(), 'b')
            ->leftJoin(DB_PREFIX . 'banner_image', 'bi', 'b.id = bi.banner_id')
            ->where("b.id = ?", [$banner_id])
            ->where("b.status = ?", [1])
            ->where("bi.language_id = ?", [$language_id])
            ->orderBy("bi.sort_order","ASC")
            ->select('bi.title', 'bi.link', 'bi.image', 'bi.sort_order');

        return $this->dao->executeQuery($query);
    }
}