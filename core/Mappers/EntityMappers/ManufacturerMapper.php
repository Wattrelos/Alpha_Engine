<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Manufacturer;

/**
 * ManufacturerMapper - Gerencia a persistência de Fabricantes/Marcas.
 * 
 * Alpha Engine:
 * - Implementa o padrão Data Mapper com Entidades.
 * - Suporte a multi-loja via joins nativos.
 * - Integração com o QueryBuilder para proteção contra SQL Injection.
 */
class ManufacturerMapper extends BaseMapper
{
    protected string $entityClass = Manufacturer::class;
    protected string $tableName = 'manufacturer';

    /**
     * Recupera fabricantes vinculados a uma loja específica.
     */
    public function getManufacturers(int $storeId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'manufacturer', 'm')
            ->join(DB_PREFIX . 'manufacturer_to_store', 'm2s', 'm.id = m2s.manufacturer_id')
            ->where('m2s.store_id = ?', [$storeId])
            ->orderBy('m.name', 'ASC')
            ->select('m.*');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Obtém os dados de um fabricante validando o vínculo com a loja.
     */
    public function getManufacturer(int $id, int $storeId): ?array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'manufacturer', 'm')
            ->join(DB_PREFIX . 'manufacturer_to_store', 'm2s', 'm.id = m2s.manufacturer_id')
            ->where('m.id = ?', [$id])
            ->where('m2s.store_id = ?', [$storeId])
            ->select('m.*');

        $results = $this->dao->executeQuery($builder);
        return $results ? $results[0] : null;
    }

    /**
     * Resolve o layout customizado para o fabricante.
     */
    public function getLayoutId(int $manufacturerId, int $storeId): int
    {
        $query = "SELECT layout_id FROM " . DB_PREFIX . "manufacturer_to_layout 
                  WHERE manufacturer_id = " . (int)$manufacturerId . " AND store_id = " . (int)$storeId;
        
        $result = $this->dao->getConnection()->query($query)->fetch_assoc();
        return $result ? (int)$result['layout_id'] : 0;
    }
}