<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\UnitOfWork;

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
            ->where('NOT (p.quantity <= 0 AND p.stock_status_id = 5)')
            ->groupBy('m.id')
            ->orderBy('m.name', 'ASC')
            ->select('m.*');

        return $this->dao->executeQuery($query);
    }

    /**
     * Retorna a listagem de fabricantes filtrada e paginada para o painel admin.
     */
    public function getAdminManufacturersPaginated(array $filters, int $page, int $limit, int $storeId): array
    {
        $start = max(0, ($page - 1) * $limit);

        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'manufacturer', 'm')
            ->join(DB_PREFIX . 'manufacturer_to_store', 'm2s', 'm.id = m2s.manufacturer_id')
            ->where('m2s.store_id = ?', [$storeId]);

        if (!empty($filters['filter_name'])) {
            $builder->where('m.name LIKE ?', ['%' . $filters['filter_name'] . '%']);
        }

        $count = $this->dao->executeCount($builder);

        $builder->select('m.*')
            ->orderBy('m.name', 'ASC')
            ->limit($limit)
            ->offset($start);

        $rows = $this->dao->executeQuery($builder);

        return [
            'total' => $count,
            'data'  => $rows
        ];
    }

    /**
     * Busca um fabricante completo com SEO Keyword para edição.
     */
    public function getAdminManufacturerForEdit(int $manufacturerId, int $storeId, int $languageId): ?array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'manufacturer', 'm')
            ->where('m.id = ?', [$manufacturerId])
            ->select(
                'm.*',
                "(SELECT keyword FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'manufacturer_id' AND `value` = CAST(m.id AS CHAR) AND store_id = " . (int)$storeId . " AND language_id = " . (int)$languageId . " LIMIT 1) AS seo_keyword"
            );

        $results = $this->dao->executeQuery($builder);
        return $results ? $results[0] : null;
    }

    /**
     * Cria um novo fabricante de forma atômica.
     */
    public function createManufacturer(array $data, int $storeId, int $languageId): int
    {
        $uow = new UnitOfWork();
        $manufacturerId = 0;

        $uow->transaction(function () use ($data, $storeId, $languageId, &$manufacturerId) {
            $name = trim($data['name'] ?? '');
            $image = $data['image'] ?? '';
            $sortOrder = (int)($data['sort_order'] ?? 0);

            $sqlMan = "INSERT INTO `" . DB_PREFIX . "manufacturer` (`name`, `image`, `sort_order`) VALUES (?, ?, ?)";
            $this->dao->executeRawSQL($sqlMan, [$name, $image, $sortOrder]);

            $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
            $manufacturerId = (int)$conn->lastInsertId();

            $sqlStore = "INSERT INTO `" . DB_PREFIX . "manufacturer_to_store` (`manufacturer_id`, `store_id`) VALUES (?, ?)";
            $this->dao->executeRawSQL($sqlStore, [$manufacturerId, $storeId]);

            $seoKeyword = trim($data['seo_keyword'] ?? '');
            if (!empty($seoKeyword)) {
                $sqlSeo = "INSERT INTO `" . DB_PREFIX . "seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES (?, ?, 'manufacturer_id', CAST(? AS CHAR), ?)";
                $this->dao->executeRawSQL($sqlSeo, [$storeId, $languageId, $manufacturerId, $seoKeyword]);
            }
        });

        return $manufacturerId;
    }

    /**
     * Atualiza um fabricante de forma atômica.
     */
    public function updateManufacturer(int $manufacturerId, array $data, int $storeId, int $languageId): bool
    {
        $uow = new UnitOfWork();

        return (bool)$uow->transaction(function () use ($manufacturerId, $data, $storeId, $languageId) {
            $existing = $this->getAdminManufacturerForEdit($manufacturerId, $storeId, $languageId);
            if (!$existing) {
                return false;
            }

            $name = trim($data['name'] ?? '');
            $sortOrder = (int)($data['sort_order'] ?? 0);
            $currentImagePath = $existing['image'] ?? '';
            $removeImage = isset($data['remove_image']) && $data['remove_image'] == '1';
            $newImagePath = $removeImage ? '' : (!empty($data['image']) ? $data['image'] : $currentImagePath);

            $sqlUpd = "UPDATE `" . DB_PREFIX . "manufacturer` SET `name` = ?, `image` = ?, `sort_order` = ? WHERE `id` = ?";
            $this->dao->executeRawSQL($sqlUpd, [$name, $newImagePath, $sortOrder, $manufacturerId]);

            $sqlSeoDel = "DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'manufacturer_id' AND `value` = CAST(? AS CHAR)";
            $this->dao->executeRawSQL($sqlSeoDel, [$manufacturerId]);

            $seoKeyword = trim($data['seo_keyword'] ?? '');
            if (!empty($seoKeyword)) {
                $sqlSeo = "INSERT INTO `" . DB_PREFIX . "seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES (?, ?, 'manufacturer_id', CAST(? AS CHAR), ?)";
                $this->dao->executeRawSQL($sqlSeo, [$storeId, $languageId, $manufacturerId, $seoKeyword]);
            }

            return true;
        });
    }

    /**
     * Exclui um fabricante de forma atômica.
     */
    public function deleteManufacturer(int $manufacturerId): bool
    {
        $uow = new UnitOfWork();

        return (bool)$uow->transaction(function () use ($manufacturerId) {
            $sqlUpdProd = "UPDATE `" . DB_PREFIX . "product` SET `manufacturer_id` = NULL WHERE `manufacturer_id` = ?";
            $this->dao->executeRawSQL($sqlUpdProd, [$manufacturerId]);

            $sqlStoreDel = "DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE `manufacturer_id` = ?";
            $this->dao->executeRawSQL($sqlStoreDel, [$manufacturerId]);

            $sqlLayoutDel = "DELETE FROM `" . DB_PREFIX . "manufacturer_to_layout` WHERE `manufacturer_id` = ?";
            $this->dao->executeRawSQL($sqlLayoutDel, [$manufacturerId]);

            $sqlSeoDel = "DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'manufacturer_id' AND `value` = CAST(? AS CHAR)";
            $this->dao->executeRawSQL($sqlSeoDel, [$manufacturerId]);

            $sqlManDel = "DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE `id` = ?";
            $this->dao->executeRawSQL($sqlManDel, [$manufacturerId]);

            return true;
        });
    }
}