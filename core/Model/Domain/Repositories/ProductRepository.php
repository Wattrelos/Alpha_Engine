<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Model\Domain\InterfaceEntity;

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

        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        $product = $mapper->getProduct($productId, $this->language_id, $this->store_id, $customerGroupId);
        
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

        // Resolve Image Model (Legacy Bridge temporário)
        if (!$this->registry->has('model_tool_image')) {
            $this->load->model('tool/image');
        }

        $data = $product_info;

        // Formatação de Imagens (Popup e Thumb)
        if ($data['image'] && is_file(DIR_IMAGE . html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'))) {
            $data['popup'] = $this->model_tool_image->resize($data['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height'));
            $data['thumb'] = $this->model_tool_image->resize($data['image'], $this->config->get('config_image_thumb_width'), $this->config->get('config_image_thumb_height'));
        } else {
            $data['popup'] = '';
            $data['thumb'] = '';
        }

        // Galeria de Imagens Adicionais
        $data['images'] = [];
        foreach ($mapper->getImages($productId) as $result) {
            if ($result['image'] && is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
                $data['images'][] = [
                    'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')),
                    'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
                ];
            }
        }

        // Formatação de Preços e Impostos
        $data['price_raw']   = $data['price'];
        $data['special_raw'] = $data['special'];
        
        $data['price'] = false;
        $data['special'] = false;
        $data['tax'] = false;

        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $data['price'] = $this->currency->format($this->tax->calculate($data['price_raw'], $data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            
            if ((float)$data['special_raw']) {
                $data['special'] = $this->currency->format($this->tax->calculate($data['special_raw'], $data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            }

            if ($this->config->get('config_tax')) {
                $data['tax'] = $this->currency->format((float)$data['special_raw'] ? $data['special_raw'] : $data['price_raw'], $this->session->data['currency']);
            }
        }

        // Descontos Progressivos
        $data['discounts'] = [];
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            foreach ($mapper->getDiscounts($productId, $customerGroupId) as $discount) {
                $data['discounts'][] = [
                    'quantity' => $discount['quantity'],
                    'price'    => $this->currency->format($this->tax->calculate($discount['price'], $data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
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
                        $price = $this->currency->format($this->tax->calculate($option_value['price'], $data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    }
                    $image = ($option_value['image'] && is_file(DIR_IMAGE . html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8'))) ? $option_value['image'] : '';
                    
                    $product_option_value_data[] = [
                        'image' => $image ? $this->model_tool_image->resize($image, 50, 50) : '',
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

        // Assinaturas (Placeholder Interoperável)
        $data['subscription_plans'] = [];

        // Textos Dinâmicos Base
        $this->loadLanguage('product/product');
        $data['heading_title'] = $data['name'];
        $data['stock'] = $data['stock_status_text'] ?? ($data['quantity'] > 0 ? $this->language->get('text_instock') : $this->language->get('text_out_of_stock'));
        $data['text_minimum'] = sprintf($this->language->get('text_minimum'), $data['minimum'] ?? 1);
        $data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', 'language=' . $this->config->get('config_language')), $this->url->link('account/register', 'language=' . $this->config->get('config_language')));
        $data['text_reviews'] = sprintf($this->language->get('text_reviews'), (int)($data['reviews'] ?? 0));

        return $data;
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

        /** @var \Alpha\Mappers\EntityMappers\ProductMapper $mapper */
        $mapper = $this->mapperFactory->get(ProductMapper::class);
        return $mapper->getProducts($filterData, $this->language_id, $this->store_id, $customerGroupId);
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
        return $this->mapperFactory->get(ProductMapper::class)->findOneBy($criteria);
    }
}