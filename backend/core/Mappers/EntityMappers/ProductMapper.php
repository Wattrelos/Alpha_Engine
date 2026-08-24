<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\ProductReport;
use Alpha\Model\Domain\Entities\Product;

/**
 * Mapper para gerenciar a complexa lógica de Produtos
 */
class ProductMapper extends BaseMapper {

    protected string $tableName = 'product';
    protected string $entityClass = Product::class;

    /**
     * Obtém um produto específico
     */
    public function getProduct(int $product_id, int $language_id, int $store_id, int $customer_group_id, array $priceStatements = []): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_to_store', 'p2s')
            ->leftJoin(DB_PREFIX . 'product', 'p', 'p.id = p2s.product_id')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->leftJoin(DB_PREFIX . 'manufacturer', 'm', 'p.manufacturer_id = m.id')
            ->where("p.id = ?", [$product_id])
            ->where("p.status = ?", [1])
            ->where("NOT (p.quantity <= 0 AND p.stock_status_id = 5)")
            ->where("p.date_available <= ?", [date('Y-m-d')])
            ->where("p2s.store_id = ?", [$store_id])
            ->where("pd.language_id = ?", [$language_id])
            ->select(
                'p.*', 
                'pd.name', 
                'pd.description', 
                'pd.meta_title', 
                'pd.meta_description', 
                'pd.meta_keyword', 
                'pd.tag', 
                'p.image', 
                'm.name AS manufacturer', 
                'm.image AS manufacturer_logo', 
                '(SELECT COUNT(*) FROM ' . DB_PREFIX . 'review r WHERE r.product_id = p.id AND r.status = 1) AS reviews'
            );
            
        if (!empty($priceStatements)) {
            $query->select(...array_values($priceStatements));
        }

        $results = $this->dao->executeQuery($query);
        
        if (!$results) return [];

        $product = $results[0];
        
        // Resolve SEO URL para produto único
        /** @var \Alpha\Model\Domain\Repositories\SeoUrlRepository $seoRepository */
        $seoRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\SeoUrlRepository::class);
        $keyword = $seoRepository->getKeywordByQuery('product_id', (string)$product_id, $store_id, $language_id);
        $product['href'] = $keyword ? '/' . $keyword : '/produto/' . $product_id;

        return $product;
    }

    /**
     * Atualiza apenas a quantidade
     */
    public function updateQuantity(int $product_id, int $quantity): void {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = "UPDATE `" . DB_PREFIX . "product` SET `quantity` = ? WHERE `id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([(int)$quantity, (int)$product_id]);
    }

    /**
     * Lista produtos com filtros dinâmicos
     */
    public function getProducts(array $data, int $language_id, int $store_id, int $customer_group_id, array $priceStatements = []): array {
        $query = new QueryBuilder();

        // Construção dinâmica da base (FROM)
        if (!empty($data['filter_category_id'])) {
            $query->from(DB_PREFIX . 'category_to_store', 'c2s');
            if (!empty($data['filter_sub_category'])) {
                $query->leftJoin(DB_PREFIX . 'category_path', 'cp', 'cp.category_id = c2s.category_id');
                $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p2c.category_id = cp.category_id');
            } else {
                $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p2c.category_id = c2s.category_id');
            }
            $query->leftJoin(DB_PREFIX . 'product_to_store', 'p2s', 'p2s.product_id = p2c.product_id');
            $query->where("c2s.store_id = ?", [$store_id]);
        } else {
            $query->from(DB_PREFIX . 'product_to_store', 'p2s');
        }

        $query->leftJoin(DB_PREFIX . 'product', 'p', 'p.id = p2s.product_id')
              ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
              ->leftJoin(DB_PREFIX . 'manufacturer', 'm', 'p.manufacturer_id = m.id')
              ->where("p2s.store_id = ?", [$store_id])
              ->where("p.status = ?", [1])
              ->where("p.date_available <= ?", [date('Y-m-d')])
              ->where("pd.language_id = ?", [$language_id])
              ->where("NOT (p.quantity <= 0 AND p.stock_status_id = 5)")
              ->where("p.master_id = 0");

        // Filtros de busca
        if (!empty($data['filter_name'])) {
            $rawSearch = trim($data['filter_name']);
            $ftQueryStr = $this->prepareFullTextQuery($rawSearch);

            if (mb_strlen($rawSearch) >= 3 && !empty($ftQueryStr)) {
                $query->where("(MATCH(pd.name, pd.description, pd.tag) AGAINST(? IN BOOLEAN MODE) OR p.model LIKE ?)", [$ftQueryStr, "%" . $rawSearch . "%"]);
            } else {
                $query->where("(pd.name LIKE ? OR pd.tag LIKE ? OR p.model LIKE ?)", ["%" . $rawSearch . "%", "%" . $rawSearch . "%", "%" . $rawSearch . "%"]);
            }
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $query->where("p.manufacturer_id = ?", [$data['filter_manufacturer_id']]);
        }

        if (!empty($data['filter_category_id'])) {
             $query->where(!empty($data['filter_sub_category']) ? "cp.path_id = ?" : "p2c.category_id = ?", [$data['filter_category_id']]);
        }

        // Filtros facetados adicionais
        if (empty($data['filter_category_id']) && !empty($data['filter_categories'])) {
            $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p2c.product_id = p2s.product_id');
        }

        if (!empty($data['filter_categories']) && is_array($data['filter_categories'])) {
            $categories = array_map('intval', $data['filter_categories']);
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $query->where("p2c.category_id IN ($placeholders)", $categories);
        }

        if (!empty($data['filter_manufacturers']) && is_array($data['filter_manufacturers'])) {
            $manufacturers = array_map('intval', $data['filter_manufacturers']);
            $placeholders = implode(',', array_fill(0, count($manufacturers), '?'));
            $query->where("p.manufacturer_id IN ($placeholders)", $manufacturers);
        }

        if (!empty($data['filter_price_min'])) {
            $query->where("p.price >= ?", [(float)$data['filter_price_min']]);
        }

        if (!empty($data['filter_price_max'])) {
            $query->where("p.price <= ?", [(float)$data['filter_price_max']]);
        }

        if (!empty($data['filter_rating'])) {
            $query->where("(SELECT AVG(r.rating) FROM " . DB_PREFIX . "review r WHERE r.product_id = p.id AND r.status = 1) >= ?", [(int)$data['filter_rating']]);
        }

        // Select e Ordenação
        $query->select(
            'p.*', 
            'pd.name', 
            'pd.description', 
            'p.image', 
            'm.name AS manufacturer', 
            'm.image AS manufacturer_logo', 
            '(SELECT COUNT(*) FROM ' . DB_PREFIX . 'review r WHERE r.product_id = p.id AND r.status = 1) AS reviews',
            '(SELECT MIN(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END) FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)) AS min_variant_price',
            '(SELECT MAX(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END) FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)) AS max_variant_price',
            '(SELECT pdv.name FROM `' . DB_PREFIX . 'product` pv LEFT JOIN `' . DB_PREFIX . 'product_description` pdv ON (pv.id = pdv.product_id AND pdv.language_id = pd.language_id) WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5) ORDER BY CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END ASC, pv.id ASC LIMIT 1) AS min_variant_name',
            '(SELECT pv.image FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5) ORDER BY CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END ASC, pv.id ASC LIMIT 1) AS min_variant_image'
        );
        if (!empty($priceStatements)) {
            $query->select(...array_values($priceStatements));
        }
        $query->groupBy('p.id');

        // Ordenação
        $sort_data = [
            'pd.name',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.date_added'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                $query->orderBy("LCASE(" . $data['sort'] . ")", $data['order'] ?? 'ASC');
            } elseif ($data['sort'] == 'p.price') {
                $query->orderBy("(CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)", $data['order'] ?? 'ASC');
            } else {
                $query->orderBy($data['sort'], $data['order'] ?? 'ASC');
            }
        } else {
            $query->orderBy('p.sort_order', 'ASC');
        }

        // Paginação
        $limit = max(1, (int)($data['limit'] ?? 20));
        $start = max(0, (int)($data['start'] ?? 0));
        
        $query->limit($limit)->offset($start);

        $results = $this->dao->executeQuery($query);

        // Alpha Engine Optimization: Resolve slugs em lote para a listagem
        if ($results) {
            $productIds = array_column($results, 'id');
            /** @var \Alpha\Model\Domain\Repositories\SeoUrlRepository $seoRepository */
            $seoRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\SeoUrlRepository::class);
            $seoRepository->primeCache($productIds, 'product_id', $store_id, $language_id);

            foreach ($results as &$result) {
                $productId = (int)$result['id'];
                $keyword = $seoRepository->getKeywordByQuery('product_id', (string)$productId, $store_id, $language_id);
                
                // Injeta o link amigável ou rota padrão
                $result['href'] = $keyword ? '/' . $keyword : '/produto/' . $productId;
            }
        }

        return $results;
    }

    /**
     * Obtém produtos específicos por uma lista de IDs (Batch Load).
     * Utilizado pela HomeRepository para carregar vitrines evitando N+1 queries.
     */
    public function getProductsByIds(array $product_ids, int $language_id, int $store_id, int $customer_group_id = 0, array $priceStatements = []): array {
        if (empty($product_ids)) return [];
        
        // Alpha Engine: Desduplica os IDs para evitar repetição de Placeholders IN(?,?,?) na mesma query
        $product_ids = array_values(array_unique($product_ids));
        
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_to_store', 'p2s')
            ->leftJoin(DB_PREFIX . 'product', 'p', 'p.id = p2s.product_id')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->leftJoin(DB_PREFIX . 'manufacturer', 'm', 'p.manufacturer_id = m.id')
            ->where("p.id IN ($placeholders)", $product_ids)
            ->where("p.status = ?", [1])
            ->where("NOT (p.quantity <= 0 AND p.stock_status_id = 5)")
            ->where("p.date_available <= ?", [date('Y-m-d')])
            ->where("p2s.store_id = ?", [$store_id])
            ->where("pd.language_id = ?", [$language_id])
            ->select(
                'p.*', 
                'pd.name', 
                'pd.description', 
                'p.image', 
                'm.name AS manufacturer', 
                'm.image AS manufacturer_logo', 
                '(SELECT COUNT(*) FROM ' . DB_PREFIX . 'review r WHERE r.product_id = p.id AND r.status = 1) AS reviews',
                '(SELECT MIN(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END) FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)) AS min_variant_price',
                '(SELECT MAX(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END) FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)) AS max_variant_price',
                '(SELECT pdv.name FROM `' . DB_PREFIX . 'product` pv LEFT JOIN `' . DB_PREFIX . 'product_description` pdv ON (pv.id = pdv.product_id AND pdv.language_id = pd.language_id) WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5) ORDER BY CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END ASC, pv.id ASC LIMIT 1) AS min_variant_name',
                '(SELECT pv.image FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5) ORDER BY CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END ASC, pv.id ASC LIMIT 1) AS min_variant_image'
            );
            
        if (!empty($priceStatements)) {
            $query->select(...array_values($priceStatements));
        }

        $results = $this->dao->executeQuery($query);

        // Alpha Engine: Resolve slugs em lote para os destaques
        if ($results) {
            /** @var \Alpha\Model\Domain\Repositories\SeoUrlRepository $seoRepository */
            $seoRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\SeoUrlRepository::class);
            $seoRepository->primeCache($product_ids, 'product_id', $store_id, $language_id);

            foreach ($results as &$result) {
                $productId = (int)$result['id'];
                $keyword = $seoRepository->getKeywordByQuery('product_id', (string)$productId, $store_id, $language_id);
                
                $result['href'] = $keyword ? '/' . $keyword : '/produto/' . $productId;
            }
        }

        return $results;
    }

    /**
     * Conta o total de produtos com base nos filtros
     */
    public function getTotalProducts(array $data, int $language_id, int $store_id): int {
        $query = new QueryBuilder();

        if (!empty($data['filter_category_id'])) {
            $query->from(DB_PREFIX . 'category_to_store', 'c2s');
            if (!empty($data['filter_sub_category'])) {
                $query->leftJoin(DB_PREFIX . 'category_path', 'cp', 'cp.category_id = c2s.category_id');
                $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p2c.category_id = cp.category_id');
            } else {
                $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p2c.category_id = c2s.category_id');
            }
            $query->leftJoin(DB_PREFIX . 'product_to_store', 'p2s', 'p2s.product_id = p2c.product_id');
        } else {
            $query->from(DB_PREFIX . 'product_to_store', 'p2s');
        }

        $query->leftJoin(DB_PREFIX . 'product', 'p', 'p.id = p2s.product_id')
              ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
              ->where("p2s.store_id = ?", [$store_id])
              ->where("p.status = ?", [1])
              ->where("p.date_available <= ?", [date('Y-m-d')])
              ->where("pd.language_id = ?", [$language_id])
              ->where("NOT (p.quantity <= 0 AND p.stock_status_id = 5)")
              ->where("p.master_id = 0");

        if (!empty($data['filter_name'])) {
            $rawSearch = trim($data['filter_name']);
            $ftQueryStr = $this->prepareFullTextQuery($rawSearch);

            if (mb_strlen($rawSearch) >= 3 && !empty($ftQueryStr)) {
                $query->where("(MATCH(pd.name, pd.description, pd.tag) AGAINST(? IN BOOLEAN MODE) OR p.model LIKE ?)", [$ftQueryStr, "%" . $rawSearch . "%"]);
            } else {
                $query->where("(pd.name LIKE ? OR pd.tag LIKE ? OR p.model LIKE ?)", ["%" . $rawSearch . "%", "%" . $rawSearch . "%", "%" . $rawSearch . "%"]);
            }
        }

        if (!empty($data['filter_category_id'])) {
            $query->where(!empty($data['filter_sub_category']) ? "cp.path_id = ?" : "p2c.category_id = ?", [$data['filter_category_id']]);
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $query->where("p.manufacturer_id = ?", [$data['filter_manufacturer_id']]);
        }

        // Filtros facetados adicionais
        if (empty($data['filter_category_id']) && !empty($data['filter_categories'])) {
            $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p2c.product_id = p2s.product_id');
        }

        if (!empty($data['filter_categories']) && is_array($data['filter_categories'])) {
            $categories = array_map('intval', $data['filter_categories']);
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $query->where("p2c.category_id IN ($placeholders)", $categories);
        }

        if (!empty($data['filter_manufacturers']) && is_array($data['filter_manufacturers'])) {
            $manufacturers = array_map('intval', $data['filter_manufacturers']);
            $placeholders = implode(',', array_fill(0, count($manufacturers), '?'));
            $query->where("p.manufacturer_id IN ($placeholders)", $manufacturers);
        }

        if (!empty($data['filter_price_min'])) {
            $query->where("p.price >= ?", [(float)$data['filter_price_min']]);
        }

        if (!empty($data['filter_price_max'])) {
            $query->where("p.price <= ?", [(float)$data['filter_price_max']]);
        }

        if (!empty($data['filter_rating'])) {
            $query->where("(SELECT AVG(r.rating) FROM " . DB_PREFIX . "review r WHERE r.product_id = p.id AND r.status = 1) >= ?", [(int)$data['filter_rating']]);
        }

        return $this->dao->executeCount($query);
    }

    /**
     * Obtém os códigos de identificação do produto (EAN, ISBN, etc)
     */
    public function getCodes(int $product_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_code', 'pc')
            ->leftJoin(DB_PREFIX . 'identifier', 'i', 'pc.code = i.code')
            ->where('pc.product_id = ?', [$product_id])
            ->where("pc.value != ''", [])
            ->select('pc.code', 'pc.value', 'i.status');

        return $this->dao->executeQuery($query);
    }

    /**
     * Obtém a galeria de imagens adicionais do produto
     */
    public function getImages(int $product_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_image')
            ->where('product_id = ?', [$product_id])
            ->orderBy('sort_order', 'ASC')
            ->select('*');

        return $this->dao->executeQuery($query);
    }

    /**
     * Obtém produtos relacionados hidratados com preços e avaliações
     */
    public function getRelated(int $product_id, int $language_id, int $store_id, int $customer_group_id, array $priceStatements = []): array {

        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_related', 'pr')
            // Join com p2s para garantir que o relacionado pertence à loja atual
            ->leftJoin(DB_PREFIX . 'product_to_store', 'p2s', 'p2s.product_id = pr.related_id')
            ->leftJoin(DB_PREFIX . 'product', 'p', 'p.id = pr.related_id')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->leftJoin(DB_PREFIX . 'manufacturer', 'm', 'p.manufacturer_id = m.id')
            ->where('pr.product_id = ?', [$product_id])
            ->where('p2s.store_id = ?', [$store_id])
            ->where('pd.language_id = ?', [$language_id])
            ->where('p.status = 1')
            ->where('NOT (p.quantity <= 0 AND p.stock_status_id = 5)')
            ->where('p.date_available <= ?', [date('Y-m-d')])
            ->select(
                'p.*', 
                'pd.name', 
                'p.image',
                'm.name AS manufacturer',
                'm.image AS manufacturer_logo',
                '(SELECT COUNT(*) FROM ' . DB_PREFIX . 'review r WHERE r.product_id = p.id AND r.status = 1) AS reviews',
                '(SELECT MIN(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END) FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)) AS min_variant_price',
                '(SELECT MAX(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END) FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)) AS max_variant_price',
                '(SELECT pdv.name FROM `' . DB_PREFIX . 'product` pv LEFT JOIN `' . DB_PREFIX . 'product_description` pdv ON (pv.id = pdv.product_id AND pdv.language_id = pd.language_id) WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5) ORDER BY CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END ASC, pv.id ASC LIMIT 1) AS min_variant_name',
                '(SELECT pv.image FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1 AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5) ORDER BY CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END ASC, pv.id ASC LIMIT 1) AS min_variant_image'
            );
            
        if (!empty($priceStatements)) {
            $query->select(...array_values($priceStatements));
        }

        $results = $this->dao->executeQuery($query);

        // Resolve slugs para produtos relacionados
        if ($results) {
            $productIds = array_column($results, 'id');
            /** @var \Alpha\Model\Domain\Repositories\SeoUrlRepository $seoRepository */
            $seoRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\SeoUrlRepository::class);
            $seoRepository->primeCache($productIds, 'product_id', $store_id, $language_id);

            foreach ($results as &$result) {
                $productId = (int)$result['id'];
                $keyword = $seoRepository->getKeywordByQuery('product_id', (string)$productId, $store_id, $language_id);
                
                $result['href'] = $keyword ? '/' . $keyword : '/produto/' . $productId;
            }
        }

        return $results;
    }

    /**
     * Obtém os atributos (especificações técnicas) do produto agrupados
     */
    public function getAttributes(int $product_id, int $language_id): array {
        // Alpha Engine: Otimização N+1. Em vez de buscar grupos e depois atributos em loop,
        // buscamos tudo em uma única query e agrupamos em PHP.
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_attribute', 'pa')
            ->leftJoin(DB_PREFIX . 'attribute', 'a', 'pa.attribute_id = a.id')
            ->leftJoin(DB_PREFIX . 'attribute_description', 'ad', 'a.id = ad.attribute_id')
            ->leftJoin(DB_PREFIX . 'attribute_group', 'ag', 'a.attribute_group_id = ag.id')
            ->leftJoin(DB_PREFIX . 'attribute_group_description', 'agd', 'ag.id = agd.attribute_group_id')
            ->where('pa.product_id = ?', [$product_id])
            ->where('pa.language_id = ?', [$language_id])
            ->where('ad.language_id = ?', [$language_id])
            ->where('agd.language_id = ?', [$language_id])
            ->orderBy('ag.sort_order', 'ASC')
            ->orderBy('agd.name', 'ASC')
            ->orderBy('a.sort_order', 'ASC')
            ->orderBy('ad.name', 'ASC')
            ->select(
                'ag.id AS attribute_group_id',
                'agd.name AS group_name',
                'a.id AS attribute_id',
                'ad.name AS attribute_name',
                'pa.text'
            );

        $results = $this->dao->executeQuery($query);

        if (empty($results)) {
            return [];
        }

        $groupedData = [];
        foreach ($results as $row) {
            $groupId = $row['attribute_group_id'];
            if (!isset($groupedData[$groupId])) {
                $groupedData[$groupId] = [
                    'attribute_group_id' => $groupId,
                    'name'               => $row['group_name'],
                    'attribute'          => []
                ];
            }
            $groupedData[$groupId]['attribute'][] = [
                'attribute_id' => $row['attribute_id'],
                'name'         => $row['attribute_name'],
                'text'         => $row['text']
            ];
        }
        return array_values($groupedData);
    }

    /**
     * Obtém todas as variações filhas de um produto pai
     */
    public function getProductVariants(int $product_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product', 'p')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->where("p.master_id = ?", [$product_id])
            ->where("pd.language_id = ?", [$language_id])
            ->select('p.*', 'pd.name', 'pd.description');

        return $this->dao->executeQuery($query);
    }

    /**
     * Obtém as opções (variantes de compra) do produto
     */
    public function getOptions(int $product_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_option', 'po')
            ->leftJoin(DB_PREFIX . 'option', 'o', 'po.option_id = o.id')
            ->leftJoin(DB_PREFIX . 'option_description', 'od', 'o.id = od.option_id')
            ->where('po.product_id = ?', [$product_id])
            ->where('od.language_id = ?', [$language_id])
            ->orderBy('o.sort_order', 'ASC')
            ->select('po.*', 'po.id AS product_option_id', 'od.name', 'o.type', 'o.sort_order');

        $rows = $this->dao->executeQuery($query);
        
        if (empty($rows)) {
            return [];
        }

        // Alpha Engine: Batch Loading para eliminar N+1 Queries nos valores das opções
        $productOptionIds = array_column($rows, 'product_option_id');
        $placeholders = implode(',', array_fill(0, count($productOptionIds), '?'));

        $queryValues = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_option_value', 'pov')
            ->leftJoin(DB_PREFIX . 'option_value', 'ov', 'pov.option_value_id = ov.id')
            ->leftJoin(DB_PREFIX . 'option_value_description', 'ovd', 'ov.id = ovd.option_value_id')
            ->where('pov.product_id = ?', [$product_id])
            ->where("pov.product_option_id IN ($placeholders)", $productOptionIds)
            ->where('ovd.language_id = ?', [$language_id])
            ->orderBy('ov.sort_order', 'ASC')
            ->select('pov.*', 'pov.id AS product_option_value_id', 'ovd.name', 'ov.image', 'ov.sort_order');

        $values = $this->dao->executeQuery($queryValues);

        // Indexação em memória O(1)
        $valuesGrouped = [];
        foreach ($values as $value) {
            $valuesGrouped[$value['product_option_id']][] = $value;
        }

        $data = [];
        foreach ($rows as $row) {
            $optionId = $row['product_option_id'];
            $data[] = $row + ['product_option_value' => $valuesGrouped[$optionId] ?? []];
        }
        
        return $data;
    }

    /**
     * Alpha Engine: Carrega valores de opções selecionadas em lote (Batch Loading).
     * Vital para o CartRepository calcular preços finais sem causar N+1 Queries no Carrinho.
     */
    public function getOptionValuesByIds(array $product_option_value_ids, int $language_id): array {
        if (empty($product_option_value_ids)) return [];
        
        $placeholders = implode(',', array_fill(0, count($product_option_value_ids), '?'));
        
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_option_value', 'pov')
            ->leftJoin(DB_PREFIX . 'option_value_description', 'ovd', 'pov.option_value_id = ovd.option_value_id')
            ->leftJoin(DB_PREFIX . 'product_option', 'po', 'pov.product_option_id = po.id')
            ->leftJoin(DB_PREFIX . 'option_description', 'od', 'po.option_id = od.option_id')
            ->where("pov.id IN ($placeholders)", $product_option_value_ids)
            ->where('ovd.language_id = ?', [$language_id])
            ->where('od.language_id = ?', [$language_id])
            ->select(
                'pov.*', 
                'pov.id AS product_option_value_id', 
                'ovd.name AS option_value_name',
                'po.option_id',
                'od.name AS option_name'
            );
            
        $results = $this->dao->executeQuery($query);
        
        // Retorna indexado pelo ID do valor da opção para facilitar o mapeamento no CartRepository
        $indexed = [];
        foreach ($results as $result) {
            $indexed[$result['product_option_value_id']] = $result;
        }
        
        return $indexed;
    }

    /**
     * Fallback de segurança legado para buscar uma opção única
     */
    public function getOptionValue(int $product_id, int $product_option_value_id, int $language_id): array {
        $result = $this->getOptionValuesByIds([$product_option_value_id], $language_id);
        return !empty($result) ? reset($result) : [];
    }

    /**
     * Obtém a tabela de descontos progressivos (por quantidade) do produto
     */
    public function getDiscounts(int $product_id, int $customer_group_id): array {
        $today = date('Y-m-d');

        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_discount', 'pd')
            ->leftJoin(DB_PREFIX . 'product', 'p', 'p.id = pd.product_id')
            ->where('pd.product_id = ?', [$product_id])
            ->where('pd.customer_group_id = ?', [$customer_group_id])
            ->where('pd.quantity > ?', [1])
            ->where("((pd.date_start IS NULL OR pd.date_start = '0000-00-00' OR pd.date_start <= ?) AND (pd.date_end IS NULL OR pd.date_end = '0000-00-00' OR pd.date_end >= ?))", [$today, $today])
            ->orderBy('pd.quantity', 'ASC')
            ->orderBy('pd.priority', 'ASC')
            ->orderBy('pd.price', 'ASC')
            ->select(
                'pd.*',
                "(CASE 
                    WHEN pd.type = 'P' THEN (p.price - (p.price * (pd.price / 100))) 
                    WHEN pd.type = 'S' THEN (p.price - pd.price) 
                    ELSE pd.price 
                END) AS price"
            );

        return $this->dao->executeQuery($query);
    }

    /**
     * Registra um acesso/relatório do produto seguindo o padrão Alpha Engine
     */
    public function addReport(int $product_id, int $store_id, string $ip, string $country = ''): void {
        $report = new ProductReport();
        $report->setProductId($product_id)
               ->setStoreId($store_id)
               ->setIp($ip)
               ->setCountry($country)
               ->setDateAdded(date('Y-m-d H:i:s'));

        $this->dao->create($report);
    }

    /**
     * Obtém os planos de assinatura do produto
     */
    public function getSubscriptions(int $product_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_subscription', 'ps')
            ->leftJoin(DB_PREFIX . 'subscription_plan', 'sp', 'ps.subscription_plan_id = sp.id')
            ->leftJoin(DB_PREFIX . 'subscription_plan_description', 'spd', 'sp.id = spd.subscription_plan_id')
            ->where('ps.product_id = ?', [$product_id])
            ->where('spd.language_id = ?', [$language_id])
            ->where('sp.status = ?', [1])
            ->select('ps.*', 'sp.frequency', 'sp.duration', 'sp.cycle', 'sp.trial_status', 'sp.trial_frequency', 'sp.trial_duration', 'sp.trial_cycle', 'spd.name');

        return $this->dao->executeQuery($query);
    }

    /**
     * Prepara e sanitiza a string de busca para ser utilizada no MySQL IN BOOLEAN MODE.
     * Transforma "smart tv" em "+smart* +tv*", removendo caracteres reservadores de sintaxe booleana.
     */
    public function prepareFullTextQuery(string $searchTerm): string {
        $cleanTerm = preg_replace('/[+\-><()~*\"@]+/', ' ', $searchTerm);
        $words = preg_split('/\s+/', trim($cleanTerm));
        $formattedWords = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word) >= 1) {
                if (mb_strlen($word) >= 2) {
                    $formattedWords[] = '+' . $word . '*';
                } else {
                    $formattedWords[] = '+' . $word;
                }
            }
        }
        return implode(' ', $formattedWords);
    }

    /**
     * Retorna a listagem paginada e filtrada de produtos para o Admin.
     */
    public function getAdminProductsPaginated(array $filters, int $page, int $limit, int $languageId): array
    {
        $start = max(0, ($page - 1) * $limit);

        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'product', 'p')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id AND pd.language_id = ' . (int)$languageId)
            ->where('p.master_id = 0');

        if (!empty($filters['filter_name'])) {
            $builder->where('pd.name LIKE ?', ['%' . $filters['filter_name'] . '%']);
        }

        if (!empty($filters['filter_ean'])) {
            $builder->where('p.ean = ?', [$filters['filter_ean']]);
        }

        if (!empty($filters['filter_category_id'])) {
            $builder->where('p.id IN (SELECT product_id FROM `' . DB_PREFIX . 'product_to_category` WHERE category_id = ?)', [(int)$filters['filter_category_id']]);
        }

        if (!empty($filters['filter_manufacturer_id'])) {
            $builder->where('p.manufacturer_id = ?', [(int)$filters['filter_manufacturer_id']]);
        }

        if (isset($filters['filter_status']) && $filters['filter_status'] !== '') {
            $builder->where('p.status = ?', [(int)$filters['filter_status']]);
        }

        $count = $this->dao->executeCount($builder);

        $builder->select(
            'p.id',
            'p.image',
            'pd.name',
            'p.model',
            'p.price',
            'p.quantity',
            'p.status',
            '(SELECT MIN(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END) FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1) AS min_variant_price',
            '(SELECT MAX(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END) FROM `' . DB_PREFIX . 'product` pv WHERE pv.master_id = p.id AND pv.status = 1) AS max_variant_price'
        )
        ->orderBy('pd.name', 'ASC')
        ->limit($limit)
        ->offset($start);

        $rows = $this->dao->executeQuery($builder);

        return [
            'total' => $count,
            'data'  => $rows
        ];
    }

    /**
     * Busca dados completos de um produto para formulário de edição do Admin.
     */
    public function getAdminProductForEdit(int $productId, int $languageId): ?array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'product', 'p')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id AND pd.language_id = ' . (int)$languageId)
            ->where('p.id = ?', [$productId])
            ->select('p.*', 'pd.name', 'pd.description', 'pd.tag', 'pd.meta_title', 'pd.meta_description', 'pd.meta_keyword');

        $rows = $this->dao->executeQuery($builder);
        return $rows ? $rows[0] : null;
    }

    /**
     * Retorna os IDs de categorias vinculados ao produto.
     */
    public function getAdminProductCategoryIds(int $productId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_to_category')
            ->where('product_id = ?', [$productId])
            ->select('category_id');

        $rows = $this->dao->executeQuery($builder);
        return array_column($rows, 'category_id');
    }

    /**
     * Retorna a lista de status de estoque para formulários.
     */
    public function getStockStatuses(int $languageId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'stock_status')
            ->where('language_id = ?', [$languageId])
            ->orderBy('name', 'ASC')
            ->select('id', 'name');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Retorna a lista de classes de peso para selects no Admin.
     */
    public function getWeightClasses(int $languageId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'weight_class', 'wc')
            ->leftJoin(DB_PREFIX . 'weight_class_description', 'wcd', 'wc.id = wcd.weight_class_id AND wcd.language_id = ' . (int)$languageId)
            ->where('wcd.title IS NOT NULL')
            ->orderBy('wc.id', 'ASC')
            ->select('wc.id', 'wcd.title', 'wcd.unit');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Retorna a lista de classes de medida/comprimento para selects no Admin.
     */
    public function getLengthClasses(int $languageId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'length_class', 'lc')
            ->leftJoin(DB_PREFIX . 'length_class_description', 'lcd', 'lc.id = lcd.length_class_id AND lcd.language_id = ' . (int)$languageId)
            ->where('lcd.title IS NOT NULL')
            ->orderBy('lc.id', 'ASC')
            ->select('lc.id', 'lcd.title', 'lcd.unit');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Retorna a lista de classes de impostos para selects no Admin.
     */
    public function getTaxClasses(): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'tax_class')
            ->orderBy('title', 'ASC')
            ->select('id', 'title', 'description');

        return $this->dao->executeQuery($builder);
    }

    /**
     * Cria um produto e seus dados correlatos de forma atômica.
     */
    public function createAdminProduct(array $data, int $storeId, int $languageId): int
    {
        $uow = new \Alpha\Model\DataAccessObject\UnitOfWork();
        $productId = 0;

        $uow->transaction(function () use ($data, $storeId, $languageId, &$productId) {
            $name = trim($data['name'] ?? '');
            $description = $data['description'] ?? '';
            $tag = trim($data['tag'] ?? '');
            $metaTitle = trim($data['meta_title'] ?? '');
            if (empty($metaTitle)) {
                $metaTitle = $name;
            }
            $metaDescription = trim($data['meta_description'] ?? '');
            $metaKeyword = trim($data['meta_keyword'] ?? '');

            $model = trim($data['model'] ?? '');
            $sku = trim($data['sku'] ?? '');
            $upc = trim($data['upc'] ?? '');
            $ean = trim($data['ean'] ?? '');
            $jan = trim($data['jan'] ?? '');
            $isbn = trim($data['isbn'] ?? '');
            $mpn = trim($data['mpn'] ?? '');
            $location = trim($data['location'] ?? '');
            $price = (float)($data['price'] ?? 0.0);
            $quantity = (int)($data['quantity'] ?? 0);
            $minimum = max(1, (int)($data['minimum'] ?? 1));
            $subtract = isset($data['subtract']) ? (int)$data['subtract'] : 1;
            $status = isset($data['status']) ? (int)$data['status'] : 1;
            $stockStatusId = (int)($data['stock_status_id'] ?? 7);
            $manufacturerId = (int)($data['manufacturer_id'] ?? 0);
            $dbManufacturerId = $manufacturerId === 0 ? null : $manufacturerId;
            $shipping = isset($data['shipping']) ? (int)$data['shipping'] : 1;
            $points = (int)($data['points'] ?? 0);
            $taxClassId = (int)($data['tax_class_id'] ?? 0);
            $sortOrder = (int)($data['sort_order'] ?? 0);

            $dateAvailable = trim($data['date_available'] ?? '');
            if (empty($dateAvailable)) {
                $dateAvailable = date('Y-m-d');
            }

            $weight = (float)($data['weight'] ?? 0.0);
            $weightClassId = (int)($data['weight_class_id'] ?? 1);
            $length = (float)($data['length'] ?? 0.0);
            $width = (float)($data['width'] ?? 0.0);
            $height = (float)($data['height'] ?? 0.0);
            $lengthClassId = (int)($data['length_class_id'] ?? 1);

            $ncm = trim($data['ncm'] ?? '');
            $cest = trim($data['cest'] ?? '');

            $imagePath = $data['image'] ?? '';
            $categoryIds = isset($data['product_category']) && is_array($data['product_category']) ? $data['product_category'] : [];

            // 1. Inserção na tabela product
            $sqlProd = "INSERT INTO `" . DB_PREFIX . "product` (
                `master_id`, `model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, 
                `variant`, `override`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, 
                `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, 
                `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, 
                `minimum`, `rating`, `sort_order`, `status`, `date_added`, `date_modified`, `ncm`, `cest`
            ) VALUES (
                0, ?, ?, ?, ?, ?, ?, ?, ?, 
                '', '', ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, 
                ?, 0, ?, ?, NOW(), NOW(), ?, ?
            )";
            $this->dao->executeRawSQL($sqlProd, [
                $model, $sku, $upc, $ean, $jan, $isbn, $mpn, $location,
                $quantity, $stockStatusId, $imagePath, $dbManufacturerId,
                $shipping, $price, $points, $taxClassId, $dateAvailable, $weight,
                $weightClassId, $length, $width, $height, $lengthClassId, $subtract,
                $minimum, $sortOrder, $status, $ncm, $cest
            ]);

            $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
            $productId = (int)$conn->lastInsertId();

            // 2. Inserção nas descrições de idioma
            $stmtLangs = $conn->query("SELECT id FROM `" . DB_PREFIX . "language`");
            $languages = $stmtLangs->fetchAll(\PDO::FETCH_COLUMN);

            $sqlDesc = "INSERT INTO `" . DB_PREFIX . "product_description` (`product_id`, `language_id`, `name`, `description`, `tag`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            foreach ($languages as $langId) {
                $this->dao->executeRawSQL($sqlDesc, [$productId, $langId, $name, $description, $tag, $metaTitle, $metaDescription, $metaKeyword]);
            }

            // 3. Inserção em product_to_store
            $effectiveStoreId = $storeId > 0 ? $storeId : 1;
            $sqlStore = "INSERT INTO `" . DB_PREFIX . "product_to_store` (`product_id`, `store_id`) VALUES (?, ?)";
            $this->dao->executeRawSQL($sqlStore, [$productId, $effectiveStoreId]);

            // 4. Inserção em product_to_category
            if (!empty($categoryIds)) {
                $sqlCat = "INSERT INTO `" . DB_PREFIX . "product_to_category` (`product_id`, `category_id`) VALUES (?, ?)";
                foreach ($categoryIds as $catId) {
                    $this->dao->executeRawSQL($sqlCat, [$productId, (int)$catId]);
                }
            }
        });

        return $productId;
    }

    /**
     * Atualiza um produto e suas variações/categorias de forma atômica.
     */
    public function updateAdminProduct(int $productId, array $data, int $storeId, int $languageId): bool
    {
        $uow = new \Alpha\Model\DataAccessObject\UnitOfWork();

        return (bool)$uow->transaction(function () use ($productId, $data, $storeId, $languageId) {
            $name = trim($data['name'] ?? '');
            $description = $data['description'] ?? '';
            $tag = trim($data['tag'] ?? '');
            $metaTitle = trim($data['meta_title'] ?? '');
            if (empty($metaTitle)) {
                $metaTitle = $name;
            }
            $metaDescription = trim($data['meta_description'] ?? '');
            $metaKeyword = trim($data['meta_keyword'] ?? '');

            $model = trim($data['model'] ?? '');
            $sku = trim($data['sku'] ?? '');
            $upc = trim($data['upc'] ?? '');
            $ean = trim($data['ean'] ?? '');
            $jan = trim($data['jan'] ?? '');
            $isbn = trim($data['isbn'] ?? '');
            $mpn = trim($data['mpn'] ?? '');
            $location = trim($data['location'] ?? '');
            $price = (float)($data['price'] ?? 0.0);
            $quantity = (int)($data['quantity'] ?? 0);
            $minimum = max(1, (int)($data['minimum'] ?? 1));
            $subtract = isset($data['subtract']) ? (int)$data['subtract'] : 1;
            $status = isset($data['status']) ? (int)$data['status'] : 1;
            $stockStatusId = (int)($data['stock_status_id'] ?? 0);
            $manufacturerId = (int)($data['manufacturer_id'] ?? 0);
            $dbManufacturerId = $manufacturerId === 0 ? null : $manufacturerId;
            $shipping = isset($data['shipping']) ? (int)$data['shipping'] : 1;
            $points = (int)($data['points'] ?? 0);
            $taxClassId = (int)($data['tax_class_id'] ?? 0);
            $sortOrder = (int)($data['sort_order'] ?? 0);

            $dateAvailable = trim($data['date_available'] ?? '');
            if (empty($dateAvailable)) {
                $dateAvailable = date('Y-m-d');
            }

            $weight = (float)($data['weight'] ?? 0.0);
            $weightClassId = (int)($data['weight_class_id'] ?? 1);
            $length = (float)($data['length'] ?? 0.0);
            $width = (float)($data['width'] ?? 0.0);
            $height = (float)($data['height'] ?? 0.0);
            $lengthClassId = (int)($data['length_class_id'] ?? 1);

            $ncm = trim($data['ncm'] ?? '');
            $cest = trim($data['cest'] ?? '');

            $existing = $this->getAdminProductForEdit($productId, $languageId);
            if (!$existing) {
                return false;
            }

            $currentImagePath = $existing['image'] ?? '';
            $removeImage = isset($data['remove_image']) && $data['remove_image'] == '1';
            $newImagePath = $removeImage ? '' : (!empty($data['image']) ? $data['image'] : $currentImagePath);

            // 1. Atualiza tabela principal product
            $sqlUpd = "UPDATE `" . DB_PREFIX . "product` SET 
                `model` = ?, 
                `sku` = ?,
                `upc` = ?,
                `ean` = ?,
                `jan` = ?,
                `isbn` = ?,
                `mpn` = ?,
                `location` = ?,
                `price` = ?, 
                `quantity` = ?, 
                `minimum` = ?,
                `subtract` = ?,
                `points` = ?,
                `tax_class_id` = ?,
                `shipping` = ?,
                `weight` = ?,
                `weight_class_id` = ?,
                `length` = ?,
                `width` = ?,
                `height` = ?,
                `length_class_id` = ?,
                `sort_order` = ?,
                `status` = ?, 
                `stock_status_id` = ?, 
                `manufacturer_id` = ?, 
                `date_available` = ?, 
                `image` = ?, 
                `ncm` = ?,
                `cest` = ?,
                `date_modified` = NOW() 
            WHERE `id` = ?";
            $this->dao->executeRawSQL($sqlUpd, [
                $model, $sku, $upc, $ean, $jan, $isbn, $mpn, $location,
                $price, $quantity, $minimum, $subtract, $points, $taxClassId,
                $shipping, $weight, $weightClassId, $length, $width, $height, $lengthClassId,
                $sortOrder, $status, $stockStatusId, $dbManufacturerId, $dateAvailable,
                $newImagePath, $ncm, $cest, $productId
            ]);

            // 2. Atualiza product_description
            $sqlDesc = "UPDATE `" . DB_PREFIX . "product_description` SET 
                `name` = ?, 
                `description` = ?,
                `tag` = ?,
                `meta_title` = ?,
                `meta_description` = ?,
                `meta_keyword` = ?
            WHERE `product_id` = ? AND `language_id` = ?";
            $this->dao->executeRawSQL($sqlDesc, [$name, $description, $tag, $metaTitle, $metaDescription, $metaKeyword, $productId, $languageId]);

            // 3. Processa Variações (Produtos Filhos)
            if (isset($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $index => $v) {
                    $vId = (int)($v['id'] ?? 0);
                    $vName = trim($v['name'] ?? '');
                    $vSku = trim($v['sku'] ?? '');
                    $vPrice = (float)($v['price'] ?? 0.0);
                    $vQuantity = (int)($v['quantity'] ?? 0);
                    $vStatus = isset($v['status']) ? (int)$v['status'] : 1;
                    $vDelete = isset($v['delete']) && $v['delete'] == '1';

                    if (empty($vName)) {
                        continue;
                    }

                    if ($vId > 0) {
                        if ($vDelete) {
                            $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "product` WHERE `id` = ? AND `master_id` = ?", [$vId, $productId]);
                            $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "product_description` WHERE `product_id` = ?", [$vId]);
                            $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE `product_id` = ?", [$vId]);
                            $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = ?", [$vId]);
                        } else {
                            $vExisting = $this->getAdminProductForEdit($vId, $languageId);
                            $vCurrentImagePath = $vExisting['image'] ?? '';
                            $vRemoveImage = isset($v['remove_image']) && $v['remove_image'] == '1';
                            $vNewImagePath = $vRemoveImage ? '' : (!empty($v['image']) ? $v['image'] : $vCurrentImagePath);

                            $sqlVUpd = "UPDATE `" . DB_PREFIX . "product` SET `sku` = ?, `price` = ?, `quantity` = ?, `status` = ?, `variant` = ?, `model` = ?, `stock_status_id` = ?, `manufacturer_id` = ?, `date_available` = ?, `image` = ?, `date_modified` = NOW() WHERE `id` = ? AND `master_id` = ?";
                            $this->dao->executeRawSQL($sqlVUpd, [$vSku, $vPrice, $vQuantity, $vStatus, $vName, $model . '-' . $vSku, $stockStatusId, $dbManufacturerId, $dateAvailable, $vNewImagePath, $vId, $productId]);

                            $vFullName = $name . ' - ' . $vName;
                            $this->dao->executeRawSQL("UPDATE `" . DB_PREFIX . "product_description` SET `name` = ?, `description` = ? WHERE `product_id` = ?", [$vFullName, $description, $vId]);
                        }
                    } elseif (!$vDelete) {
                        $vNewImagePath = $v['image'] ?? '';
                        $sqlVIns = "INSERT INTO `" . DB_PREFIX . "product` (`master_id`, `model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `variant`, `override`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, `minimum`, `rating`, `sort_order`, `status`, `date_added`, `date_modified`, `ncm`, `cest`) VALUES (?, ?, ?, '', '', '', '', '', '', ?, '', ?, ?, ?, ?, 1, ?, 0, 0, ?, 0.00000000, 0, 0.00000000, 0.00000000, 0.00000000, 0, 1, 1, 0, 0, ?, NOW(), NOW(), '', '')";
                        $this->dao->executeRawSQL($sqlVIns, [$productId, $model . '-' . $vSku, $vSku, $vName, $vQuantity, $stockStatusId, $vNewImagePath, $dbManufacturerId, $vPrice, $dateAvailable, $vStatus]);

                        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
                        $newVariantId = (int)$conn->lastInsertId();

                        $stmtLangs = $conn->query("SELECT id FROM `" . DB_PREFIX . "language`");
                        $languages = $stmtLangs->fetchAll(\PDO::FETCH_COLUMN);

                        $vFullName = $name . ' - ' . $vName;
                        foreach ($languages as $langId) {
                            $this->dao->executeRawSQL("INSERT INTO `" . DB_PREFIX . "product_description` (`product_id`, `language_id`, `name`, `description`, `tag`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, ?, ?, ?, '', ?, '', '')", [$newVariantId, $langId, $vFullName, $description, $vFullName]);
                        }

                        $effectiveStoreId = $storeId > 0 ? $storeId : 1;
                        $this->dao->executeRawSQL("INSERT INTO `" . DB_PREFIX . "product_to_store` (`product_id`, `store_id`) VALUES (?, ?)", [$newVariantId, $effectiveStoreId]);
                    }
                }
            }

            // 4. Edição em Lote para variações
            $this->dao->executeRawSQL("UPDATE `" . DB_PREFIX . "product` SET `manufacturer_id` = ?, `stock_status_id` = ?, `date_available` = ?, `date_modified` = NOW() WHERE `master_id` = ?", [$dbManufacturerId, $stockStatusId, $dateAvailable, $productId]);

            // 5. Atualizar categorias do pai e sincronizar com filhas
            $categoryIds = isset($data['product_category']) && is_array($data['product_category']) ? $data['product_category'] : [];
            $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = ?", [$productId]);
            if (!empty($categoryIds)) {
                foreach ($categoryIds as $catId) {
                    $this->dao->executeRawSQL("INSERT INTO `" . DB_PREFIX . "product_to_category` (`product_id`, `category_id`) VALUES (?, ?)", [$productId, (int)$catId]);
                }
            }

            $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` IN (SELECT `id` FROM `" . DB_PREFIX . "product` WHERE `master_id` = ?)", [$productId]);
            $this->dao->executeRawSQL("INSERT INTO `" . DB_PREFIX . "product_to_category` (`product_id`, `category_id`) SELECT p.id, pc.category_id FROM `" . DB_PREFIX . "product` p JOIN `" . DB_PREFIX . "product_to_category` pc ON pc.product_id = ? WHERE p.master_id = ?", [$productId, $productId]);

            return true;
        });
    }

    /**
     * Exclui um produto e suas tabelas secundárias de forma atômica.
     */
    public function deleteAdminProduct(int $productId): bool
    {
        $uow = new \Alpha\Model\DataAccessObject\UnitOfWork();

        return (bool)$uow->transaction(function () use ($productId) {
            $tables = [
                'product_attribute', 'product_code', 'product_description', 'product_discount',
                'product_filter', 'product_image', 'product_option', 'product_option_value',
                'product_report', 'product_reward', 'product_subscription', 'product_to_category',
                'product_to_layout', 'product_to_store', 'product_viewed', 'review', 'cart',
                'customer_wishlist', 'coupon_product', 'subscription_product'
            ];

            foreach ($tables as $tbl) {
                $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "{$tbl}` WHERE `product_id` = ?", [$productId]);
            }

            $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `product_id` = ? OR `related_id` = ?", [$productId, $productId]);
            $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'product_id' AND `value` = CAST(? AS CHAR)", [$productId]);
            $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "product` WHERE `id` = ?", [$productId]);

            return true;
        });
    }
}
