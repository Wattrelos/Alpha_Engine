<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\DataTransferObject\ViewResponse;

/**
 * ProductRepository - Autoridade de Domínio para Produtos.
 * 
 * Centraliza a recuperação de produtos, delegando a lógica de persistência 
 * ao ProductMapper e garantindo que o domínio trabalhe com entidades tipadas.
 */
class ProductRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Alpha Engine: Recupera o Grafo Completo do Produto (Detalhes)
     * 
     * @param int $productId
     * @return array|null
     */
    public function getProduct(int $productId): ?array
    {
        $customerGroupId = $this->customer->isLogged() 
            ? (int)$this->customer->getGroupId() 
            : (int)$this->config->get('config_customer_group_id');

        // CacheStrategy: Variação por ID, Idioma, Loja e Grupo de Desconto
        $cacheKey = "product.{$productId}.{$this->language_id}.{$this->store_id}.{$customerGroupId}";

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        /** @var \Alpha\Model\Domain\Repositories\PriceRepository $priceRepo */
        $priceRepo = $this->registry->get('alpha_repository_factory')->get(PriceRepository::class);
        $priceStatements = $priceRepo->getPriceStatements($customerGroupId);

        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        $product = $mapper->getProduct($productId, $this->language_id, $this->store_id, $customerGroupId, $priceStatements);
        
        if ($product && $this->cache !== null) {
            // TTL Curto (5 min) devido à volatilidade de estoque e preço
            $this->cache->set($cacheKey, $product, 300);
        }

        return $product;
    }

    /**
     * Alpha Engine: Prepara e Orquestra o DTO completo para a página de produto (Skinny Controller).
     * Executa Batch Loading de opções, imagens, preços e aplica impostos nativamente.
     * 
     * @param int $productId
     * @return array|null
     */
    public function getProductDisplayData(int $productId): ?array
    {
        $product_info = $this->getProduct($productId);

        if (!$product_info) {
            return null;
        }

        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        $languageId = $this->language_id;
        $customerGroupId = $this->customer->isLogged() ? (int)$this->customer->getGroupId() : (int)$this->config->get('config_customer_group_id');

        // Alpha Engine: Image Presenter centraliza e limpa a resolução de imagens e placeholders
        $imagePresenter = new \Alpha\Support\Presenters\ImagePresenter($this->registry);

        $data = $product_info;

        // Formatação de Imagens (Popup e Thumb)
        $data['popup'] = $imagePresenter->resize($data['image'], (int)$this->config->get('config_image_popup_width'), (int)$this->config->get('config_image_popup_height'), false);
        $data['thumb'] = $imagePresenter->resize($data['image'], (int)$this->config->get('config_image_thumb_width'), (int)$this->config->get('config_image_thumb_height'), false);

        // Galeria de Imagens Adicionais
        $data['images'] = [];
        foreach ($mapper->getImages($productId) as $result) {
            if ($result['image']) {
                $popup = $imagePresenter->resize($result['image'], (int)$this->config->get('config_image_popup_width'), (int)$this->config->get('config_image_popup_height'), false);
                if ($popup) {
                    $data['images'][] = [
                        'popup' => $popup,
                        'thumb' => $imagePresenter->resize($result['image'], (int)$this->config->get('config_image_additional_width'), (int)$this->config->get('config_image_additional_height'), false)
                    ];
                }
            }
        }

        // Formatação de Preços e Impostos
        $data['price_raw']   = $data['price'] ?? 0.0;
        $data['special_raw'] = $data['special'] ?? false;
        
        $data['price'] = false;
        $data['special'] = false;
        $data['tax'] = false;

        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $data['price'] = $this->currency->format($this->tax->calculate($data['price_raw'], $data['tax_class_id'] ?? 0, $this->config->get('config_tax')), $this->session->data['currency']);
            
            if ((float)$data['special_raw']) {
                $data['special'] = $this->currency->format($this->tax->calculate($data['special_raw'], $data['tax_class_id'] ?? 0, $this->config->get('config_tax')), $this->session->data['currency']);
            }

            if ($this->config->get('config_tax')) {
                $data['tax'] = $this->currency->format((float)$data['special_raw'] ? (float)$data['special_raw'] : (float)$data['price_raw'], $this->session->data['currency']);
            }
        }

        // Descontos Progressivos
        $data['discounts'] = [];
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            foreach ($mapper->getDiscounts($productId, $customerGroupId) as $discount) {
                $data['discounts'][] = [
                    'quantity' => $discount['quantity'],
                    'price'    => $this->currency->format($this->tax->calculate($discount['price'], $data['tax_class_id'] ?? 0, $this->config->get('config_tax')), $this->session->data['currency'])
                ] + $discount;
            }
        }

        // Opções Dinâmicas (Batch Loading de valores e modificadores de preço)
        $data['options'] = [];
        foreach ($mapper->getOptions($productId, $languageId) as $option) {
            $product_option_value_data = [];
            foreach ($option['product_option_value'] as $option_value) {
                if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                    $price = false;
                    if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
                        $price = $this->currency->format($this->tax->calculate($option_value['price'], $data['tax_class_id'] ?? 0, $this->config->get('config_tax')), $this->session->data['currency']);
                    }
                    
                    $product_option_value_data[] = [
                        'image' => $imagePresenter->resize($option_value['image'], 50, 50, false),
                        'price' => $price
                    ] + $option_value;
                }
            }
            $data['options'][] = ['product_option_value' => $product_option_value_data] + $option;
        }

        // Atributos Técnicos e Códigos (EAN, ISBN)
        $data['attribute_groups'] = $mapper->getAttributes($productId, $languageId);
        
        $data['product_codes'] = [];
        foreach ($mapper->getCodes($productId) as $result) {
            if ($result['status']) {
                $data['product_codes'][] = $result;
            }
        }

        // Tags SEO
        $data['tags'] = [];
        if (!empty($data['tag'])) {
            $tags = explode(',', $data['tag']);
            foreach ($tags as $tag) {
                $data['tags'][] = [
                    'tag'  => trim($tag),
                    'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . '&tag=' . urlencode(trim($tag)))
                ];
            }
        }

        // Assinaturas
        $data['subscription_plans'] = $this->getSubscriptions($productId);

        // Textos Dinâmicos Base
        $this->loadLanguage('product/product');
        $data['heading_title'] = $data['name'];
        $data['stock'] = $data['stock_status_text'] ?? ($data['quantity'] > 0 ? $this->language->get('text_instock') : $this->language->get('text_out_of_stock'));
        $data['text_minimum'] = sprintf($this->language->get('text_minimum'), (!empty($data['minimum']) && $data['minimum'] > 0) ? $data['minimum'] : 1);
        $data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', 'language=' . $this->config->get('config_language')), $this->url->link('account/register', 'language=' . $this->config->get('config_language')));
        $data['text_reviews'] = sprintf($this->language->get('text_reviews'), (int)($data['reviews'] ?? 0));

        return $data;
    }

    /**
     * Alpha Engine: Orquestra a inteligência de negócios e formatação da Busca (Skinny Controller).
     *
     * @param array $filterData
     * @return ViewResponse
     */
    public function getSearchData(array $filterData): ViewResponse
    {
        $this->loadLanguage('product/search');

        $data = [];
        $data['search']       = $filterData['filter_name'] ?? '';
        $data['description']  = $filterData['filter_description'] ?? '';
        $data['category_id']  = $filterData['filter_category_id'] ?? 0;
        $data['sub_category'] = $filterData['filter_sub_category'] ?? '';
        $data['sort']         = $filterData['sort'] ?? 'p.sort_order';
        $data['order']        = $filterData['order'] ?? 'ASC';
        $data['limit']        = $filterData['limit'] ?? 10;

        // 1. Breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home') ?: 'Home',
            'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_search') ?: 'Busca',
            'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language'))
        ];

        // 2. Dropdown de Categorias
        /** @var \Alpha\Model\Domain\Repositories\CategoryRepository $categoryRepository */
        $categoryRepository = $this->registry->get('alpha_repository_factory')->get(\Alpha\Model\Domain\Repositories\CategoryRepository::class);
        $data['categories'] = [];
        $categories_1 = $categoryRepository->getCategories(0);
        foreach ($categories_1 as $category_1) {
            $data['categories'][] = [
                'category_id' => $category_1['id'],
                'name'        => $category_1['name']
            ];
            $categories_2 = $categoryRepository->getCategories((int)$category_1['id']);
            foreach ($categories_2 as $category_2) {
                $data['categories'][] = [
                    'category_id' => $category_2['id'],
                    'name'        => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $category_2['name']
                ];
                $categories_3 = $categoryRepository->getCategories((int)$category_2['id']);
                foreach ($categories_3 as $category_3) {
                    $data['categories'][] = [
                        'category_id' => $category_3['id'],
                        'name'        => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $category_3['name']
                    ];
                }
            }
        }

        // 3. Produtos Filtrados
        $filter = $filterData;
        $filter['start'] = (($filterData['page'] ?? 1) - 1) * $data['limit'];
        $data['products'] = $this->getProducts($filter);
        $data['product_total'] = $this->getTotalProducts($filter);

        // 4. Montagem das URLs de Base
        $url = '';
        if ($data['search']) $url .= '&search=' . urlencode(html_entity_decode($data['search'], ENT_QUOTES, 'UTF-8'));
        if (!empty($filterData['filter_tag'])) $url .= '&tag=' . urlencode(html_entity_decode($filterData['filter_tag'], ENT_QUOTES, 'UTF-8'));
        if ($data['description']) $url .= '&description=' . $data['description'];
        if ($data['category_id']) $url .= '&category_id=' . $data['category_id'];
        if ($data['sub_category']) $url .= '&sub_category=' . $data['sub_category'];

        $baseUrl = $url;

        // 5. Orquestração de Ordenações (Sorts) e Limites
        $data['sorts'] = [];
        $data['sorts'][] = ['text' => $this->language->get('text_default') ?: 'Padrão', 'value' => 'p.sort_order-ASC', 'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $baseUrl . '&sort=p.sort_order&order=ASC&limit=' . $data['limit'])];
        $data['sorts'][] = ['text' => $this->language->get('text_name_asc') ?: 'Nome (A - Z)', 'value' => 'pd.name-ASC', 'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $baseUrl . '&sort=pd.name&order=ASC&limit=' . $data['limit'])];
        $data['sorts'][] = ['text' => $this->language->get('text_name_desc') ?: 'Nome (Z - A)', 'value' => 'pd.name-DESC', 'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $baseUrl . '&sort=pd.name&order=DESC&limit=' . $data['limit'])];
        $data['sorts'][] = ['text' => $this->language->get('text_price_asc') ?: 'Preço (Menor > Maior)', 'value' => 'p.price-ASC', 'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $baseUrl . '&sort=p.price&order=ASC&limit=' . $data['limit'])];
        $data['sorts'][] = ['text' => $this->language->get('text_price_desc') ?: 'Preço (Maior > Menor)', 'value' => 'p.price-DESC', 'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $baseUrl . '&sort=p.price&order=DESC&limit=' . $data['limit'])];

        $data['limits'] = [];
        $limits = array_unique([$this->config->get('config_pagination_catalog') ?: 10, 25, 50, 75, 100]);
        sort($limits);
        foreach($limits as $value) {
            $data['limits'][] = [
                'text'  => $value,
                'value' => $value,
                'href'  => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $baseUrl . '&sort=' . $data['sort'] . '&order=' . $data['order'] . '&limit=' . $value)
            ];
        }

        // 6. Dados Brutos para o Paginated Component
        $data['pagination'] = [
            'total' => $data['product_total'],
            'page'  => $filterData['page'] ?? 1,
            'limit' => $data['limit'],
            'url'   => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $baseUrl . '&sort=' . $data['sort'] . '&order=' . $data['order'] . '&page={page}')
        ];

        return new ViewResponse($data);
    }

    /**
     * Alpha Engine: Recupera as opções configuráveis de um produto.
     * Centraliza a consulta para validação de adições ao carrinho.
     */
    public function getOptions(int $productId): array
    {
        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        return $mapper->getOptions($productId, $this->language_id);
    }

    /**
     * Alpha Engine: Recupera os planos de assinatura disponíveis para o produto.
     */
    public function getSubscriptions(int $productId): array
    {
        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        
        if (method_exists($mapper, 'getSubscriptions')) {
            return $mapper->getSubscriptions($productId, $this->language_id);
        }
        return [];
    }

    /**
     * Alpha Engine: Prepara o DTO de exibição do Thumbnail (Vitrines, Categorias, Buscas).
     * Elimina a necessidade de carregar o controlador legado 'product/thumb' em loop (Fim do N+1 Controllers).
     */
    public function getProductThumbData(array $result): array
    {
        $this->loadLanguage('product/thumb');
        $imagePresenter = new \Alpha\Support\Presenters\ImagePresenter($this->registry);

        $price = false;
        $special = false;
        $tax = false;

        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $price = $this->currency->format($this->tax->calculate($result['price'] ?? 0, $result['tax_class_id'] ?? 0, $this->config->get('config_tax')), $this->session->data['currency']);
            
            if (!empty($result['special']) && (float)$result['special']) {
                $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'] ?? 0, $this->config->get('config_tax')), $this->session->data['currency']);
            }

            if ($this->config->get('config_tax')) {
                $tax = $this->currency->format(!empty($result['special']) && (float)$result['special'] ? (float)$result['special'] : (float)($result['price'] ?? 0), $this->session->data['currency']);
            }
        }

        $description = '';
        if (isset($result['description'])) {
            $description = \Alpha\Support\AlphaString::substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, (int)$this->config->get('config_product_description_length')) . '..';
        }


        return [
            'product_id'      => $result['id'],
            'thumb'           => $imagePresenter->resize($result['image'], (int)$this->config->get('config_image_product_width'), (int)$this->config->get('config_image_product_height')),
            'name'            => $result['name'],
            'description'     => $description,
            'price'           => $price,
            'special'         => $special,
            'tax'             => $tax,
            'minimum'         => (!empty($result['minimum']) && $result['minimum'] > 0) ? $result['minimum'] : 1,
            'rating'          => (int)($result['rating'] ?? $result['reviews'] ?? 0),
            'href'            => $result['href'] ?? $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['id']),
            'text_tax'        => $this->language->get('text_tax'),
            'button_cart'     => $this->language->get('button_cart'),
            'button_wishlist' => $this->language->get('button_wishlist'),
            'button_compare'  => $this->language->get('button_compare'),
            'cart'            => $this->url->link('common/cart.info', 'language=' . $this->config->get('config_language')),
            'cart_add'        => $this->url->link('checkout/cart.add', 'language=' . $this->config->get('config_language')),
            'wishlist_add'    => $this->url->link('account/wishlist.add', 'language=' . $this->config->get('config_language')),
            'compare_add'     => $this->url->link('product/compare.add', 'language=' . $this->config->get('config_language'))
        ];
    }

    /**
     * Alpha Engine: Registra visualização de produto.
     */
    public function addReport(int $productId, string $ip): void
    {
        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        if (method_exists($mapper, 'addReport')) {
            $mapper->addReport($productId, $this->store_id, $ip);
        }
    }

    /**
     * Alpha Engine: Recupera produtos com filtros aplicados.
     */
    public function getProducts(array $filterData): array
    {
        $customerGroupId = $this->customer->isLogged() 
            ? (int)$this->customer->getGroupId() 
            : (int)$this->config->get('config_customer_group_id');

        /** @var \Alpha\Model\Domain\Repositories\PriceRepository $priceRepo */
        $priceRepo = $this->registry->get('alpha_repository_factory')->get(PriceRepository::class);
        $priceStatements = $priceRepo->getPriceStatements($customerGroupId);

        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        return $mapper->getProducts($filterData, $this->language_id, $this->store_id, $customerGroupId, $priceStatements);
    }

    /**
     * Alpha Engine: Conta o total de produtos para paginação.
     */
    public function getTotalProducts(array $filterData): int
    {
        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        return $mapper->getTotalProducts($filterData, $this->language_id, $this->store_id);
    }

    /**
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        return $this->mapperFactory->get(ProductMapper::class)->findById($id);
    }

    /**
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        return $this->mapperFactory->get(ProductMapper::class)->findAll();
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get(ProductMapper::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->mapperFactory->get(ProductMapper::class)->search($criteria);
        return $results[0] ?? null;
    }
}
