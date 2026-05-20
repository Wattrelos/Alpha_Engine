<?php
namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Mappers\CollectionToArrayConverter;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Opencart\System\Engine\Registry;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Product;
use Alpha\Model\Domain\Entities\ProductReport;

/**
 * ProductMapper - Orquestra a persistência e o enriquecimento de dados do Produto para o Catálogo.
 * 
 * Melhoras Alpha Engine:
 * - Autonomia de Dados: Métodos que antes dependiam do model legado agora residem aqui.
 * - Performance: Queries otimizadas via QueryBuilder e uso de Prepared Statements.
 * - Tipagem PHP 8.4: Retorno de arrays estruturados para o Twig e Entidades para o motor.
 */
class ProductMapper extends BaseMapper
{
    protected string $entityClass = Product::class;
    protected string $tableName = 'product';
    protected string $primaryKey = 'id';

    public function __construct(Registry $registry = null)
    {
        parent::__construct($registry);
        // O $this->dao já é inicializado pelo construtor do BaseMapper.
    }

    /**
     * Obtém a Entidade Product hidratada via DAO.
     */
    public function getProductEntity(int $product_id): ?Product {
        $product = new Product();
        $product->setId($product_id);
        $results = $this->dao->read($product);
        return $results ? $results[0] : null;
    }

    /**
     * Obtém o Grafo Completo do Produto (Domain Entity + Relacionamentos).
     * 
     * Melhora Alpha Engine:
     * - Uma única chamada ao Mapper provê todos os dados para o controlador.
     * - Utiliza a recursividade nativa do DAO para carregar imagens, opções, atributos, etc.
     */
    public function getDetailedProduct(int $product_id, int $language_id, int $store_id, int $customer_group_id = 1): array {
        // 1. Carrega a Entidade hidratada via DAO (processAssociations cuidará da carga recursiva)
        $product = $this->getProductEntity($product_id);

        if (!$product) {
            return [];
        }

        // 2. Converte a árvore de entidades (Grafo) para array associativo
        $data = CollectionToArrayConverter::convertEntity($product);

        // 3. Normalização de Descrição (Multilíngue)
        $data['name'] = '';
        $data['description'] = '';
        $data['tag'] = '';
        foreach ($product->getDescriptions() as $desc) {
            if ($desc->getLanguageId() === $language_id) {
                $data['name'] = $desc->getName();
                $data['description'] = html_entity_decode($desc->getDescription(), ENT_QUOTES, 'UTF-8');
                $data['meta_title'] = $desc->getMetaTitle();
                $data['meta_description'] = $desc->getMetaDescription();
                $data['meta_keyword'] = $desc->getMetaKeyword();
                $data['tag'] = $desc->getTag();
                break;
            }
        }

        // 4. Lógica de Status de Estoque (Business Rule)
        if ($product->getQuantity() <= 0) {
            $stock_status_id = $product->getStockStatusId();
        } else {
            $stock_status_id = 0; // Indica que deve exibir a quantidade numérica
        }
        $data['stock_status_text'] = $this->getStockStatusName($stock_status_id, $language_id);

        // 5. Resolução de Fabricante
        $data['manufacturer_name'] = $product->getManufacturer() ? $product->getManufacturer()->getName() : '';

        // 6. Preço Especial (Promoção Ativa)
        $data['special'] = 0;
        $specials = $this->getDiscounts($product_id, $customer_group_id); // Reutiliza lógica de busca de preços
        // Nota: A query de specials costuma ser específica, aqui garantimos o campo 'special' no array de retorno

        // 4. Enriquecimento de Dados Complexos (Atributos Agrupados e Relacionados)
        $data['codes'] = $this->getCodes($product_id);
        $data['attribute_groups'] = $this->getAttributes($product_id, $language_id);
        
        // Trata opções (se for variante, busca do pai)
        $master_id = $product->getMasterId() ?: $product_id;
        $data['options'] = $this->getOptions($master_id, $language_id);
        
        $data['subscriptions'] = $this->getSubscriptions($product_id, $language_id);

        // Converte as entidades relacionadas em array para manter o Grafo de getDetailedProduct amigável ao Twig
        $related_entities = $this->getRelated($product_id, $language_id, $store_id, $customer_group_id);
        $data['related_products'] = CollectionToArrayConverter::convertCollection($related_entities);

        return $data;
    }

    /**
     * Obtém os dados detalhados de um produto para a camada View (Twig).
     */
    public function getProduct(int $product_id, int $language_id, int $store_id, int $customer_group_id = 1): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product', 'p')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->leftJoin(DB_PREFIX . 'product_to_store', 'p2s', 'p.id = p2s.product_id')
            ->leftJoin(DB_PREFIX . 'manufacturer', 'm', 'p.manufacturer_id = m.id')
            ->where("p.id = ?", [$product_id])
            ->where("pd.language_id = ?", [$language_id])
            ->where("p2s.store_id = ?", [$store_id])
            ->where("p.status = ?", [1])
            ->where("p.date_available <= NOW()")
            ->select(
                'p.*', 
                'pd.name', 
                'pd.description', 
                'pd.meta_title', 
                'pd.meta_description', 
                'pd.meta_keyword', 
                'pd.tag', 
                'm.name AS manufacturer'
            );

        $results = $this->dao->executeQuery($query);
        if (!$results) {
            return [];
        }

        $row = $results[0];

        // Alpha Engine: Normalização e Tipagem para compatibilidade com controladores legados e Twig
        $row['product_id'] = (int)$row['id'];
        $row['price']      = (float)($row['price'] ?? 0);
        $row['special']    = (float)($row['special'] ?? 0);
        $row['rating']     = (int)($row['rating'] ?? 0);
        $row['reviews']    = (int)($row['reviews'] ?? 0);
        $row['minimum']    = (int)($row['minimum'] ?? 1);

        $row['variant']    = !empty($row['variant']) ? json_decode($row['variant'], true) : [];
        $row['override']   = !empty($row['override']) ? json_decode($row['override'], true) : [];

        return $row;
    }

    /**
     * Lista produtos com filtros, ordenação e paginação.
     */
    public function getProducts(array $data, int $language_id, int $store_id, int $customer_group_id = 1): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product', 'p')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->leftJoin(DB_PREFIX . 'product_to_store', 'p2s', 'p.id = p2s.product_id')
            ->where("pd.language_id = ?", [$language_id])
            ->where("p2s.store_id = ?", [$store_id])
            ->where("p.status = ?", [1])
            ->where("p.date_available <= NOW()");

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $query->leftJoin(DB_PREFIX . 'category_path', 'cp', 'p2c.category_id = cp.category_id');
                $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p.id = p2c.product_id');
                $query->where("cp.path_id = ?", [(int)$data['filter_category_id']]);
            } else {
                $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p.id = p2c.product_id');
                $query->where("p2c.category_id = ?", [(int)$data['filter_category_id']]);
            }
        }

        if (!empty($data['filter_name']) || !empty($data['filter_tag']) || !empty($data['filter_search'])) {
            $sql = "(";
            $params = [];
            
            $search_term = $data['filter_search'] ?? ($data['filter_name'] ?? '');

            if ($search_term) {
                $sql .= "pd.name LIKE ?";
                $params[] = '%' . $search_term . '%';

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE ?";
                    $params[] = '%' . $search_term . '%';
                }
            }

            $tag_term = $data['filter_tag'] ?? '';
            if ($tag_term) {
                if ($params) $sql .= " OR ";
                $sql .= "pd.tag LIKE ?";
                $params[] = '%' . $tag_term . '%';
            }
            $sql .= ")";
            $query->where($sql, $params);
        }

        $sort_data = ['pd.name', 'p.model', 'p.price', 'p.sort_order', 'p.date_added'];
        $sort = (isset($data['sort']) && in_array($data['sort'], $sort_data)) ? $data['sort'] : 'p.sort_order';
        $order = (isset($data['order']) && $data['order'] == 'DESC') ? 'DESC' : 'ASC';
        $query->orderBy($sort, $order);

        if (isset($data['start']) || isset($data['limit'])) {
            $query->limit((int)($data['limit'] ?? 20))->offset((int)($data['start'] ?? 0));
        }

        // Alpha Engine: Selecionamos apenas o ID para delegar a hidratação completa ao DAO
        $query->select('p.id');
        $results = $this->dao->executeQuery($query);
        $ids = array_map('intval', array_column($results, 'id'));
        
        return $this->getProductsByIds($ids, $language_id, $store_id);
    }

    /**
     * Alpha Engine: Recupera e normaliza múltiplos produtos em lote.
     * Ideal para Vitrines (Featured, Latest, Bestseller).
     */
    public function getProductsByIds(array $productIds, int $languageId, int $storeId): array
    {
        if (empty($productIds)) {
            return [];
        }

        $entities = $this->dao->readByIds(Product::class, $productIds);
        
        return array_map(function($entity) {
            /** @var Product $entity */
            // Utilizamos o getProduct para garantir a aplicação das regras de preço e joins de descrição
            $data = $this->getProduct($entity->getId(), $languageId, $storeId);
            // Mantemos a compatibilidade com DTOs que esperam a chave 'id'
            $data['id'] = $entity->getId();
            return $data;
        }, array_filter($entities));
    }

    /**
     * Conta o total de produtos para os filtros de busca.
     */
    public function getTotalProducts(array $data, int $language_id, int $store_id): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product', 'p')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->leftJoin(DB_PREFIX . 'product_to_store', 'p2s', 'p.id = p2s.product_id')
            ->where("pd.language_id = ?", [$language_id])
            ->where("p2s.store_id = ?", [$store_id])
            ->where("p.status = ?", [1])
            ->where("p.date_available <= NOW()");

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p.id = p2c.product_id')
                      ->leftJoin(DB_PREFIX . 'category_path', 'cp', 'p2c.category_id = cp.category_id')
                      ->where("cp.path_id = ?", [(int)$data['filter_category_id']]);
            } else {
                $query->leftJoin(DB_PREFIX . 'product_to_category', 'p2c', 'p.id = p2c.product_id')
                      ->where("p2c.category_id = ?", [(int)$data['filter_category_id']]);
            }
        }

        if (!empty($data['filter_search'])) {
            $sql = "(pd.name LIKE ? OR pd.tag LIKE ?)";
            $params = ['%' . $data['filter_search'] . '%', '%' . $data['filter_search'] . '%'];
            if (!empty($data['filter_description'])) {
                $sql = "(pd.name LIKE ? OR pd.tag LIKE ? OR pd.description LIKE ?)";
                $params[] = '%' . $data['filter_search'] . '%';
            }
            $query->where($sql, $params);
        }

        return $this->dao->executeCount($query);
    }

    /**
     * Recupera as especificações técnicas (Atributos) organizadas por grupo.
     */
    public function getAttributes(int $product_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_attribute', 'pa')
            ->leftJoin(DB_PREFIX . 'attribute', 'a', 'pa.attribute_id = a.id')
            ->leftJoin(DB_PREFIX . 'attribute_description', 'ad', 'a.id = ad.attribute_id')
            ->leftJoin(DB_PREFIX . 'attribute_group', 'ag', 'a.attribute_group_id = ag.id')
            ->leftJoin(DB_PREFIX . 'attribute_group_description', 'agd', 'ag.id = agd.attribute_group_id')
            ->where("pa.product_id = ?", [$product_id])
            ->where("ad.language_id = ?", [$language_id])
            ->where("agd.language_id = ?", [$language_id])
            ->orderBy("ag.sort_order", "ASC")
            ->orderBy("agd.name", "ASC")
            ->orderBy("a.sort_order", "ASC")
            ->orderBy("ad.name", "ASC")
            ->select('agd.name AS group_name', 'ad.name', 'pa.text');

        $results = $this->dao->executeQuery($query);
        
        $attribute_groups = [];
        foreach ($results as $result) {
            $attribute_groups[$result['group_name']]['name'] = $result['group_name'];
            $attribute_groups[$result['group_name']]['attribute'][] = [
                'name' => $result['name'],
                'text' => $result['text']
            ];
        }

        return $attribute_groups;
    }

    /**
     * Recupera as opções de compra (Cores, Tamanhos, etc) e seus valores.
     */
    public function getOptions(int $product_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_option', 'po')
            ->leftJoin(DB_PREFIX . 'option', 'o', 'po.option_id = o.id')
            ->leftJoin(DB_PREFIX . 'option_description', 'od', 'o.id = od.option_id')
            ->where("po.product_id = ?", [$product_id])
            ->where("od.language_id = ?", [$language_id])
            ->orderBy("o.sort_order", "ASC")
            ->select('po.*', 'od.name', 'o.type');

        $product_options = $this->dao->executeQuery($query);
        $option_data = [];

        foreach ($product_options as $product_option) {
            $query_value = (new QueryBuilder())
                ->from(DB_PREFIX . 'product_option_value', 'pov')
                ->leftJoin(DB_PREFIX . 'option_value', 'ov', 'pov.option_value_id = ov.id')
                ->leftJoin(DB_PREFIX . 'option_value_description', 'ovd', 'ov.id = ovd.option_value_id')
                ->where("pov.product_option_id = ?", [(int)$product_option['id']])
                ->where("ovd.language_id = ?", [$language_id])
                ->orderBy("ov.sort_order", "ASC")
                ->select('pov.*', 'ovd.name', 'ov.image');

            $option_values = $this->dao->executeQuery($query_value);
            
            $option_data[] = [
                'product_option_id'    => $product_option['id'],
                'option_id'            => $product_option['option_id'],
                'name'                 => $product_option['name'],
                'type'                 => $product_option['type'],
                'value'                => $product_option['value'],
                'required'             => $product_option['required'],
                'product_option_value' => $option_values
            ];
        }

        return $option_data;
    }

    /**
     * Recupera descontos progressivos por quantidade.
     */
    public function getDiscounts(int $product_id, int $customer_group_id = 1): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_discount')
            ->where("product_id = ?", [$product_id])
            ->where("customer_group_id = ?", [$customer_group_id])
            ->where("quantity > 1")
            ->where("((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW()))")
            ->orderBy("priority", "ASC")
            ->orderBy("price", "ASC")
            ->select('*');

        return $this->dao->executeQuery($query);
    }

    /**
     * Recupera as imagens adicionais da galeria.
     */
    public function getImages(int $product_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_image')
            ->where("product_id = ?", [$product_id])
            ->orderBy("sort_order", "ASC")
            ->select('*');

        return $this->dao->executeQuery($query);
    }

    /**
     * Recupera produtos relacionados.
     * 
     * @return Product[] Coleção de entidades hidratadas.
     */
    public function getRelated(int $product_id, int $language_id, int $store_id, int $customer_group_id = 1): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_related', 'pr')
            ->leftJoin(DB_PREFIX . 'product', 'p', 'pr.related_id = p.id')
            ->where("pr.product_id = ?", [$product_id])
            ->where("p.status = ?", [1])
            ->where("p.date_available <= NOW()")
            ->select('p.id');

        $results = $this->dao->executeQuery($query);
        $ids = array_map('intval', array_column($results, 'id'));

        return !empty($ids) ? $this->dao->readByIds(Product::class, $ids) : [];
    }

    /**
     * Recupera códigos e identificadores do produto.
     */
    public function getCodes(int $product_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product', 'p')
            ->where("p.id = ?", [$product_id])
            ->select('p.model', 'p.sku', 'p.upc', 'p.ean', 'p.jan', 'p.isbn', 'p.mpn', 'p.location');
        
        $results = $this->dao->executeQuery($query);
        if (!$results) return [];
        
        $row = $results[0];
        return [
            ['text' => 'Model', 'value' => $row['model'], 'status' => (bool)$row['model']],
            ['text' => 'SKU', 'value' => $row['sku'], 'status' => (bool)$row['sku']],
            ['text' => 'UPC', 'value' => $row['upc'], 'status' => (bool)$row['upc']],
            ['text' => 'EAN', 'value' => $row['ean'], 'status' => (bool)$row['ean']],
            ['text' => 'JAN', 'value' => $row['jan'], 'status' => (bool)$row['jan']],
            ['text' => 'ISBN', 'value' => $row['isbn'], 'status' => (bool)$row['isbn']],
            ['text' => 'MPN', 'value' => $row['mpn'], 'status' => (bool)$row['mpn']],
            ['text' => 'Location', 'value' => $row['location'], 'status' => (bool)$row['location']],
        ];
    }

    /**
     * Recupera planos de assinatura vinculados ao produto.
     */
    public function getSubscriptions(int $product_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_subscription', 'ps')
            ->leftJoin(DB_PREFIX . 'subscription_plan', 'sp', 'ps.subscription_plan_id = sp.id')
            ->leftJoin(DB_PREFIX . 'subscription_plan_description', 'spd', 'sp.id = spd.subscription_plan_id')
            ->where("ps.product_id = ?", [$product_id])
            ->where("spd.language_id = ?", [$language_id])
            ->where("sp.status = ?", [1])
            ->orderBy("sp.sort_order", "ASC")
            ->select('sp.*', 'spd.name');

        return $this->dao->executeQuery($query);
    }

    /**
     * Recupera o nome do status de estoque de forma isolada.
     */
    public function getStockStatusName(int $stock_status_id, int $language_id): string {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'stock_status')
            ->where("id = ?", [$stock_status_id])
            ->where("language_id = ?", [$language_id])
            ->select('name');
        
        $results = $this->dao->executeQuery($query);
        return $results ? $results[0]['name'] : '';
    }

    /**
     * Registra auditoria de tráfego (Visualizações).
     */
    public function addReport(int $product_id, int $store_id, string $ip): void {
        $report = new ProductReport();
        $report->setProductId($product_id)->setStoreId($store_id)->setIp($ip)->setDateAdded(date('Y-m-d H:i:s'));
        $this->dao->create($report);
    }

    /**
     * Atualiza a quantidade em estoque.
     */
    public function updateQuantity(int $product_id, int $quantity): void {
        $query = (new QueryBuilder())->update(DB_PREFIX . 'product')
            ->set('quantity', $quantity)
            ->where('id = ?', [$product_id]);
        
        $stmt = $this->dao->getConnection()->prepare($query->getSQL());
        $stmt->execute($query->getParams());
    }

    /**
     * Recupera as categorias associadas ao produto.
     */
    public function getCategories(int $product_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_to_category')
            ->where('product_id = ?', [$product_id])
            ->select('*');

        return $this->dao->executeQuery($query);
    }

    /**
     * Recupera dados de uma opção específica.
     */
    public function getOption(int $product_id, int $product_option_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_option', 'po')
            ->leftJoin(DB_PREFIX . 'option', 'o', 'po.option_id = o.id')
            ->leftJoin(DB_PREFIX . 'option_description', 'od', 'o.id = od.option_id')
            ->where('po.product_id = ?', [$product_id])
            ->where('po.id = ?', [$product_option_id])
            ->where('od.language_id = ?', [$language_id])
            ->select('po.*', 'od.name', 'o.type');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Recupera um valor de opção específico.
     */
    public function getOptionValue(int $product_id, int $product_option_value_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_option_value', 'pov')
            ->leftJoin(DB_PREFIX . 'option_value', 'ov', 'pov.option_value_id = ov.id')
            ->leftJoin(DB_PREFIX . 'option_value_description', 'ovd', 'ov.id = ovd.option_value_id')
            ->where('pov.product_id = ?', [$product_id])
            ->where('pov.id = ?', [$product_option_value_id])
            ->where('ovd.language_id = ?', [$language_id])
            ->select('pov.*', 'ovd.name', 'ov.image');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Recupera todos os valores de uma opção de produto.
     */
    public function getOptionValues(int $product_id, int $product_option_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_option_value', 'pov')
            ->leftJoin(DB_PREFIX . 'option_value', 'ov', 'pov.option_value_id = ov.id')
            ->leftJoin(DB_PREFIX . 'option_value_description', 'ovd', 'ov.id = ovd.option_value_id')
            ->where('pov.product_id = ?', [$product_id])
            ->where('pov.product_option_id = ?', [$product_option_id])
            ->where('ovd.language_id = ?', [$language_id])
            ->orderBy('ov.sort_order', 'ASC')
            ->select('pov.*', 'ovd.name', 'ov.image');

        return $this->dao->executeQuery($query);
    }

    /**
     * Recupera um plano de assinatura específico.
     */
	public function getSubscription(int $product_id, int $subscription_plan_id, int $customer_group_id, int $language_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product_subscription', 'ps')
            ->leftJoin(DB_PREFIX . 'subscription_plan', 'sp', 'ps.subscription_plan_id = sp.id')
			->leftJoin(DB_PREFIX . 'subscription_plan_description', 'spd', 'sp.id = spd.subscription_plan_id')
            ->where('ps.product_id = ?', [$product_id])
            ->where('ps.subscription_plan_id = ?', [$subscription_plan_id])
            ->where('ps.customer_group_id = ?', [$customer_group_id])
			->where('spd.language_id = ?', [$language_id])
            ->where('sp.status = ?', [1])
			->select('ps.*', 'sp.trial_status', 'sp.trial_price', 'sp.trial_cycle', 'sp.trial_frequency', 'sp.trial_duration', 'sp.price', 'sp.cycle', 'sp.frequency', 'sp.duration', 'spd.name', 'spd.description');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista promoções (Specials).
     */
    public function getSpecials(array $data, int $language_id, int $store_id, int $customer_group_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product', 'p')
            ->leftJoin(DB_PREFIX . 'product_description', 'pd', 'p.id = pd.product_id')
            ->leftJoin(DB_PREFIX . 'product_to_store', 'p2s', 'p.id = p2s.product_id')
            ->where('pd.language_id = ?', [$language_id])
            ->where('p2s.store_id = ?', [$store_id])
            ->where('p.status = ?', [1])
            ->where('p.date_available <= NOW()');

        $sort_data = ['pd.name', 'p.model', 'p.price', 'p.sort_order', 'p.date_added'];
        $sort = (isset($data['sort']) && in_array($data['sort'], $sort_data)) ? $data['sort'] : 'p.sort_order';
        $order = (isset($data['order']) && $data['order'] == 'DESC') ? 'DESC' : 'ASC';
        $query->orderBy($sort, $order);

        if (isset($data['start']) || isset($data['limit'])) {
            $query->limit((int)($data['limit'] ?? 20))->offset((int)($data['start'] ?? 0));
        }

        $query->select('p.id');
        $results = $this->dao->executeQuery($query);
        $ids = array_map('intval', array_column($results, 'id'));

        $entities = !empty($ids) ? $this->dao->readByIds(Product::class, $ids) : [];
        return array_map(fn($e) => ['product_id' => $e->getId()] + CollectionToArrayConverter::convertEntity($e), $entities);
    }

    /**
     * Conta o total de promoções.
     */
    public function getTotalSpecials(int $customer_group_id, int $store_id): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'product', 'p')
            ->leftJoin(DB_PREFIX . 'product_to_store', 'p2s', 'p.id = p2s.product_id')
            ->where('p2s.store_id = ?', [$store_id])
            ->where('p.status = ?', [1])
            ->where('p.date_available <= NOW()');
        
        return $this->dao->executeCount($query);
    }

    /**
     * Atualiza a quantidade de uma opção específica.
     */
    public function updateOptionQuantity(int $product_id, int $product_option_id, int $product_option_value_id, int $quantity): void {
        $query = (new QueryBuilder())->update(DB_PREFIX . 'product_option_value')
            ->set('quantity', $quantity)
            ->where('id = ?', [$product_option_value_id])
            ->where('product_id = ?', [$product_id]);

        $stmt = $this->dao->getConnection()->prepare($query->getSQL());
        $stmt->execute($query->getParams());
    }

    /**
     * Alpha Engine: Redimensiona uma imagem utilizando a biblioteca nativa.
     * Centraliza a lógica de tratamento de imagens no Mapper de Produto.
     */
    public function resize(string $filename, int $width, int $height): string {
        if (!$filename || !is_file(DIR_IMAGE . $filename)) {
            return '';
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $image_old = $filename;
        $image_new = 'cache/' . oc_substr($filename, 0, oc_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

        if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
            list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);

            if (!in_array($image_type, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
                return HTTP_SERVER . 'image/' . $image_old;
            }

            $path = '';
            $directories = explode('/', dirname($image_new));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            if ($width_orig != $width || $height_orig != $height) {
                $image = new \Opencart\System\Library\Image(DIR_IMAGE . $image_old);
                $image->resize($width, $height);
                $image->save(DIR_IMAGE . $image_new);
            } else {
                copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
            }
        }

        return HTTP_SERVER . 'image/' . $image_new;
    }
}