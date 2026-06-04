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
            ->where("p.quantity > 0")
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
              ->where("p2s.store_id = ?", [$store_id])
              ->where("p.status = ?", [1])
              ->where("p.date_available <= ?", [date('Y-m-d')])
              ->where("pd.language_id = ?", [$language_id])
              ->where("p.quantity > 0");

        // Filtros de busca
        if (!empty($data['filter_name'])) {
            $query->where("(pd.name LIKE ? OR p.model = ?)", ["%" . $data['filter_name'] . "%", $data['filter_name']]);
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
        $query->select('p.*', 'pd.name', 'pd.description', 'p.image', '(SELECT COUNT(*) FROM ' . DB_PREFIX . 'review r WHERE r.product_id = p.id AND r.status = 1) AS reviews');
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
            ->where("p.id IN ($placeholders)", $product_ids)
            ->where("p.status = ?", [1])
            ->where("p.quantity > 0")
            ->where("p.date_available <= ?", [date('Y-m-d')])
            ->where("p2s.store_id = ?", [$store_id])
            ->where("pd.language_id = ?", [$language_id])
            ->select('p.*', 'pd.name', 'pd.description', 'p.image', '(SELECT COUNT(*) FROM ' . DB_PREFIX . 'review r WHERE r.product_id = p.id AND r.status = 1) AS reviews');
            
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
              ->where("p.quantity > 0");

        if (!empty($data['filter_name'])) {
            $query->where("(pd.name LIKE ? OR p.model = ?)", ["%" . $data['filter_name'] . "%", $data['filter_name']]);
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
            ->where('pr.product_id = ?', [$product_id])
            ->where('p2s.store_id = ?', [$store_id])
            ->where('pd.language_id = ?', [$language_id])
            ->where('p.status = 1')
            ->where('p.quantity > 0')
            ->where('p.date_available <= ?', [date('Y-m-d')])
            ->select(
                'p.*', 
                'pd.name', 
                'p.image',
                '(SELECT COUNT(*) FROM ' . DB_PREFIX . 'review r WHERE r.product_id = p.id AND r.status = 1) AS reviews'
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
}
