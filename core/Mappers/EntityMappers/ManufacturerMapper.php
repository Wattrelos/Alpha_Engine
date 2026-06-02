<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar a lógica de Fabricantes (Manufacturers)
 */
class ManufacturerMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém um fabricante específico
     */
    public function getManufacturer(int $manufacturer_id, int $store_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'manufacturer', 'm')
            ->leftJoin(DB_PREFIX . 'manufacturer_to_store', 'm2s', 'm.id = m2s.manufacturer_id')
            ->where("m.id = ?", [(int)$manufacturer_id])
            ->where("m2s.store_id = ?", [$store_id])
            ->select('m.*');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista fabricantes com filtros, ordenação e paginação
     */
    public function getManufacturers(array $data, int $store_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'manufacturer', 'm')
            ->leftJoin(DB_PREFIX . 'manufacturer_to_store', 'm2s', 'm.id = m2s.manufacturer_id')
            ->where("m2s.store_id = ?", [$store_id]);

        $query->select('m.*');

        // Ordenação
        $sort_data = ['name', 'sort_order'];
        $sort = (isset($data['sort']) && in_array($data['sort'], $sort_data)) ? $data['sort'] : 'name';
        $order = (isset($data['order']) && $data['order'] == 'DESC') ? 'DESC' : 'ASC';
        
        $query->orderBy("m." . $sort, $order);

        // Paginação
        if (isset($data['start']) || isset($data['limit'])) {
            $limit = (int)($data['limit'] ?? 20);
            $start = (int)($data['start'] ?? 0);
            if ($start < 0) $start = 0;
            if ($limit < 1) $limit = 20;
            
            $query->limit($limit)->offset($start);
        }

        return $this->dao->executeQuery($query);
    }

    /**
     * Obtém o layout associado ao fabricante
     */
    public function getLayoutId(int $manufacturer_id, int $store_id): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'manufacturer_to_layout')
            ->where("manufacturer_id = ?", [(int)$manufacturer_id])
            ->where("store_id = ?", [(int)$store_id])
            ->select('layout_id');

        $results = $this->dao->executeQuery($query);
        return $results ? (int)$results[0]['layout_id'] : 0;
    }

    /**
     * Obtém fabricantes que possuem produtos em uma determinada categoria ou suas subcategorias
     */
    public function getManufacturersByCategory(int $category_id, int $store_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_to_category', 'p2c')
            ->join(DB_PREFIX . 'category_path', 'cp', 'p2c.category_id = cp.category_id')
            ->join(DB_PREFIX . 'product', 'p', 'p2c.product_id = p.id')
            ->join(DB_PREFIX . 'product_to_store', 'p2s', 'p.id = p2s.product_id')
            ->join(DB_PREFIX . 'manufacturer', 'm', 'p.manufacturer_id = m.id')
            ->join(DB_PREFIX . 'manufacturer_to_store', 'm2s', 'm.id = m2s.manufacturer_id')
            ->where('cp.path_id = ?', [$category_id])
            ->where('p2s.store_id = ?', [$store_id])
            ->where('m2s.store_id = ?', [$store_id])
            ->where('p.status = 1')
            ->where('p.quantity > 0')
            ->groupBy('m.id')
            ->orderBy('m.name', 'ASC')
            ->select('m.*');

        return $this->dao->executeQuery($query);
    }
}