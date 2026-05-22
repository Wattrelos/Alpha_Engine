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
     * Gera as subqueries de preço (desconto, especial, etc)
     */
    private function getPriceStatements(int $customer_group_id): array {
        return [
            'discount' => "(SELECT (CASE WHEN `pd2`.`type` = 'P' THEN (`p`.`price` - (`p`.`price` * (`pd2`.`price` / 100))) WHEN `pd2`.`type` = 'S' THEN (`p`.`price` - `pd2`.`price`) ELSE `pd2`.`price` END) FROM `" . DB_PREFIX . "product_discount` `pd2` WHERE `pd2`.`product_id` = `p`.`id` AND `pd2`.`customer_group_id` = '" . $customer_group_id . "' AND `pd2`.`quantity` = '1' AND `pd2`.`special` = '0' AND ((`pd2`.`date_start` = '0000-00-00' OR `pd2`.`date_start` < NOW()) AND (`pd2`.`date_end` = '0000-00-00' OR `pd2`.`date_end` > NOW())) ORDER BY `pd2`.`priority` ASC, `pd2`.`price` ASC LIMIT 1) AS `discount` ",
            'special'  => "(SELECT (CASE WHEN `ps`.`type` = 'P' THEN (`p`.`price` - (`p`.`price` * (`ps`.`price` / 100))) WHEN `ps`.`type` = 'S' THEN (`p`.`price` - `ps`.`price`) ELSE `ps`.`price` END) FROM `" . DB_PREFIX . "product_discount` `ps` WHERE `ps`.`product_id` = `p`.`id` AND `ps`.`customer_group_id` = '" . $customer_group_id . "' AND `ps`.`quantity` = '1' AND `ps`.`special` = '1' AND ((`ps`.`date_start` = '0000-00-00' OR `ps`.`date_start` < NOW()) AND (`ps`.`date_end` = '0000-00-00' OR `ps`.`date_end` > NOW())) ORDER BY `ps`.`priority` ASC, `ps`.`price` ASC LIMIT 1) AS `special` ",
            'reward'   => "(SELECT `pr`.`points` FROM `" . DB_PREFIX . "product_reward` `pr` WHERE `pr`.`product_id` = `p`.`id` AND `pr`.`customer_group_id` = '" . $customer_group_id . "') AS `reward` ",
            'review'   => "(SELECT COUNT(*) FROM `" . DB_PREFIX . "review` `r` WHERE `r`.`product_id` = `p`.`id` AND `r`.`status` = '1' GROUP BY `r`.`product_id`) AS `reviews` "
        ];
    }

    /**
     * Obtém um produto específico
     */
    public function getProduct(int $product_id, int $language_id, int $store_id, int $customer_group_id): array {
        $stmt = $this->getPriceStatements($customer_group_id);
        
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_to_store', 'p2s')
            ->leftJoin(DB_PREFIX . 'product', 'p', 'p.id = p2s.product_id')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->where("p.id = ?", [$product_id])
            ->where("p.status = ?", [1])
            ->where("p.date_available <= NOW()")
            ->where("p2s.store_id = ?", [$store_id])
            ->where("pd.language_id = ?", [$language_id])
            ->select('p.*', 'pd.name', 'pd.description', 'p.image', $stmt['discount'], $stmt['special'], $stmt['reward'], $stmt['review']);

        $results = $this->dao->executeQuery($query);
        
        if (!$results) return [];

        $product = $results[0];
        
        // Resolve SEO URL para produto único
        $seoMapper = new SeoUrlMapper($this->registry);
        $keyword = $seoMapper->getKeywordByQuery('product_id', (string)$product_id, $store_id, $language_id);
        $product['href'] = $keyword ?: 'index.php?route=product/product&product_id=' . $product_id;

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
    public function getProducts(array $data, int $language_id, int $store_id, int $customer_group_id): array {
        $stmt = $this->getPriceStatements($customer_group_id);
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
              ->where("p.date_available <= NOW()")
              ->where("pd.language_id = ?", [$language_id])
              ->where("p.quantity > 0");

        // Filtros de busca
        if (!empty($data['filter_search'])) {
            // Simplificado para o exemplo, mas segue a lógica de ORs do original
            $query->where("(pd.name LIKE ? OR p.model = ?)", ["%".$data['filter_search']."%", $data['filter_search']]);
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $query->where("p.manufacturer_id = ?", [$data['filter_manufacturer_id']]);
        }

        if (!empty($data['filter_category_id'])) {
             $query->where($data['filter_sub_category'] ? "cp.path_id = ?" : "p2c.category_id = ?", [$data['filter_category_id']]);
        }

        // Select e Ordenação
        $query->select('p.*', 'pd.name', 'pd.description', 'p.image', $stmt['discount'], $stmt['special'], $stmt['reward'], $stmt['review'])
              ->groupBy('p.id');

        // Paginação
        $limit = (int)($data['limit'] ?? 20);
        $start = (int)($data['start'] ?? 0);
        
        $query->limit($limit)->offset($start);

        $results = $this->dao->executeQuery($query);

        // Alpha Engine Optimization: Resolve slugs em lote para a listagem
        if ($results) {
            $productIds = array_column($results, 'id');
            $seoMapper = new SeoUrlMapper($this->registry);
            $seoMapper->primeCache($productIds, 'product_id', $store_id, $language_id);

            foreach ($results as &$result) {
                $productId = (int)$result['id'];
                $keyword = $seoMapper->getKeywordByQuery('product_id', (string)$productId, $store_id, $language_id);
                
                // Injeta o link amigável ou rota padrão
                $result['href'] = $keyword ?: 'index.php?route=product/product&product_id=' . $productId;
            }
        }

        return $results;
    }

    /**
     * Obtém produtos específicos por uma lista de IDs (Batch Load).
     * Utilizado pela HomeRepository para carregar vitrines evitando N+1 queries.
     */
    public function getProductsByIds(array $product_ids, int $language_id, int $store_id, int $customer_group_id = 0): array {
        if (empty($product_ids)) return [];
        
        $stmt = $this->getPriceStatements($customer_group_id);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_to_store', 'p2s')
            ->leftJoin(DB_PREFIX . 'product', 'p', 'p.id = p2s.product_id')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->where("p.id IN ($placeholders)", $product_ids)
            ->where("p.status = ?", [1])
            ->where("p.date_available <= NOW()")
            ->where("p2s.store_id = ?", [$store_id])
            ->where("pd.language_id = ?", [$language_id])
            ->select('p.*', 'pd.name', 'pd.description', 'p.image', $stmt['discount'], $stmt['special'], $stmt['reward'], $stmt['review']);

        $results = $this->dao->executeQuery($query);

        // Alpha Engine: Resolve slugs em lote para os destaques
        if ($results) {
            $seoMapper = new SeoUrlMapper($this->registry);
            $seoMapper->primeCache($product_ids, 'product_id', $store_id, $language_id);

            foreach ($results as &$result) {
                $productId = (int)$result['id'];
                $keyword = $seoMapper->getKeywordByQuery('product_id', (string)$productId, $store_id, $language_id);
                
                $result['href'] = $keyword ?: 'index.php?route=product/product&product_id=' . $productId;
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
              ->where("p.date_available <= NOW()")
              ->where("pd.language_id = ?", [$language_id])
              ->where("p.quantity > 0");

        if (!empty($data['filter_category_id'])) {
            $query->where($data['filter_sub_category'] ? "cp.path_id = ?" : "p2c.category_id = ?", [$data['filter_category_id']]);
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $query->where("p.manufacturer_id = ?", [$data['filter_manufacturer_id']]);
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
    public function getRelated(int $product_id, int $language_id, int $store_id, int $customer_group_id): array {
        $stmt = $this->getPriceStatements($customer_group_id);

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
            ->where('p.date_available <= NOW()')
            ->select(
                'p.*', 
                'pd.name', 
                'p.image', 
                $stmt['discount'], $stmt['special'], $stmt['reward'], $stmt['review']
            );

        $results = $this->dao->executeQuery($query);

        // Resolve slugs para produtos relacionados
        if ($results) {
            $productIds = array_column($results, 'id');
            $seoMapper = new SeoUrlMapper($this->registry);
            $seoMapper->primeCache($productIds, 'product_id', $store_id, $language_id);

            foreach ($results as &$result) {
                $productId = (int)$result['id'];
                $keyword = $seoMapper->getKeywordByQuery('product_id', (string)$productId, $store_id, $language_id);
                
                $result['href'] = $keyword ?: 'index.php?route=product/product&product_id=' . $productId;
            }
        }

        return $results;
    }

    /**
     * Obtém os atributos (especificações técnicas) do produto agrupados
     */
    public function getAttributes(int $product_id, int $language_id): array {
        $group_query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_attribute', 'pa')
            ->leftJoin(DB_PREFIX . 'attribute', 'a', 'pa.attribute_id = a.id')
            ->leftJoin(DB_PREFIX . 'attribute_group', 'ag', 'a.attribute_group_id = ag.id')
            ->leftJoin(DB_PREFIX . 'attribute_group_description', 'agd', 'ag.id = agd.attribute_group_id')
            ->where('pa.product_id = ?', [$product_id])
            ->where('agd.language_id = ?', [$language_id])
            ->groupBy('ag.id')
            ->orderBy('ag.sort_order', 'ASC')
            ->orderBy('agd.name', 'ASC')
            ->select('ag.id AS attribute_group_id', 'agd.name');

        $groups = $this->dao->executeQuery($group_query);
        $data = [];

        foreach ($groups as $group) {
            $attr_query = (new QueryBuilder())
                ->from(DB_PREFIX . 'product_attribute', 'pa')
                ->leftJoin(DB_PREFIX . 'attribute', 'a', 'pa.attribute_id = a.id')
                ->leftJoin(DB_PREFIX . 'attribute_description', 'ad', 'a.id = ad.attribute_id')
                ->where('pa.product_id = ?', [$product_id])
                ->where('a.attribute_group_id = ?', [(int)$group['attribute_group_id']])
                ->where('ad.language_id = ?', [$language_id])
                ->where('pa.language_id = ?', [$language_id])
                ->orderBy('a.sort_order', 'ASC')
                ->orderBy('ad.name', 'ASC')
                ->select('a.id AS attribute_id', 'ad.name', 'pa.text');
            
            $data[] = [
                'attribute_group_id' => $group['attribute_group_id'],
                'name'               => $group['name'],
                'attribute'          => $this->dao->executeQuery($attr_query)
            ];
        }
        return $data;
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
        $data = [];

        foreach ($rows as $row) {
            $data[] = $row + ['product_option_value' => $this->getOptionValues($product_id, (int)$row['product_option_id'], $language_id)];
        }
        return $data;
    }

    /**
     * Auxiliar para carregar os valores de uma opção específica
     */
    private function getOptionValues(int $product_id, int $product_option_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_option_value', 'pov')
            ->leftJoin(DB_PREFIX . 'option_value', 'ov', 'pov.option_value_id = ov.id')
            ->leftJoin(DB_PREFIX . 'option_value_description', 'ovd', 'ov.id = ovd.option_value_id')
            ->where('pov.product_id = ?', [$product_id])
            ->where('pov.product_option_id = ?', [$product_option_id])
            ->where('ovd.language_id = ?', [$language_id])
            ->orderBy('ov.sort_order', 'ASC')
            ->select('pov.*', 'pov.id AS product_option_value_id', 'ovd.name', 'ov.image', 'ov.sort_order');

        return $this->dao->executeQuery($query);
    }

    /**
     * Obtém a tabela de descontos progressivos (por quantidade) do produto
     */
    public function getDiscounts(int $product_id, int $customer_group_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_discount', 'pd')
            ->leftJoin(DB_PREFIX . 'product', 'p', 'p.id = pd.product_id')
            ->where('pd.product_id = ?', [$product_id])
            ->where('pd.customer_group_id = ?', [$customer_group_id])
            ->where('pd.quantity > ?', [1])
            ->where("((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW()))", [])
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
}
