<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Banner;

/**
 * BannerMapper - Centraliza a persistência da entidade Banner.
 * 
 * Melhora Alpha Engine:
 * - Tipagem Estrita: Utiliza a entidade Banner para hidratação via DAO.
 * - Performance: Preparado para o Identity Map do DataAccessObject.
 */
class BannerMapper extends BaseMapper
{
    protected string $entityClass = Banner::class;
    protected string $tableName = 'banner';

    /**
     * Recupera a entidade Banner hidratada pelo seu ID.
     */
    public function findById(int $id): ?Banner
    {
        $banner = new Banner();
        $banner->setId($id);
        
        $results = $this->dao->read($banner);
        return $results ? $results[0] : null;
    }
}