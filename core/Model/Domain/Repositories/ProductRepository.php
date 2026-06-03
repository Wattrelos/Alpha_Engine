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
        $customerGroupId = $this->getCustomerGroupId();

        // CacheStrategy: Variação por ID, Idioma, Loja e Grupo de Desconto
        $cacheKey = "product.{$productId}.{$this->language_id}.{$this->store_id}.{$customerGroupId}";

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        /** @var \Alpha\Model\Domain\Repositories\PriceRepository $priceRepo */
        $priceRepo = RepositoryFactory::getInstance()->get(PriceRepository::class);
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
        $customerGroupId = $this->getCustomerGroupId();

        $data = $product_info;

        // Galeria de Imagens Adicionais (Brutas)
        $data['images'] = $mapper->getImages($productId);

        // Regra de Negócio: Ocultar preços se configurado para visitantes
        $showPrice = $this->shouldShowPrice();
        if (!$showPrice) {
            $data['price']   = false;
            $data['special'] = false;
        }

        // Descontos Progressivos (Brutos)
        $data['discounts'] = [];
        if ($showPrice) {
            $data['discounts'] = $mapper->getDiscounts($productId, $customerGroupId);
        }

        // Opções Dinâmicas (Brutas)
        $data['options'] = $mapper->getOptions($productId, $languageId);

        // Atributos Técnicos e Códigos (EAN, ISBN)
        $data['attribute_groups'] = $mapper->getAttributes($productId, $languageId);
        
        $data['product_codes'] = [];
        foreach ($mapper->getCodes($productId) as $result) {
            if ($result['status']) {
                $data['product_codes'][] = $result;
            }
        }

        // Tags (Strings cruas)
        $data['tags'] = [];
        if (!empty($data['tag'])) {
            $tags = explode(',', $data['tag']);
            foreach ($tags as $tag) {
                $data['tags'][] = trim($tag);
            }
        }

        // Assinaturas
        $data['subscription_plans'] = $this->getSubscriptions($productId);

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
        $data = [];
        $data['search']       = $filterData['search'] ?? $filterData['filter_name'] ?? '';
        $data['description']  = $filterData['filter_description'] ?? '';
        $data['category_id']  = $filterData['filter_category_id'] ?? 0;
        $data['sub_category'] = $filterData['filter_sub_category'] ?? '';
        $data['sort']         = $filterData['sort'] ?? 'p.sort_order';
        $data['order']        = $filterData['order'] ?? 'ASC';
        $data['limit']        = $filterData['limit'] ?? 10;
        $data['page']         = $filterData['page'] ?? 1;

        // 1. Dropdown de Categorias (Otimizado: Fim do N+1 de categorias, Dados Brutos)
        /** @var \Alpha\Mappers\EntityMappers\CategoryMapper $categoryMapper */
        $categoryMapper = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\CategoryMapper::class);
        $data['categories'] = $categoryMapper->getAllCategories($this->language_id, $this->store_id);

        // 2. Produtos Filtrados Brutos
        $filter = $filterData;
        $filter['filter_name'] = $data['search'];
        $filter['start'] = ($data['page'] - 1) * $data['limit'];
        
        $data['products'] = $this->getProducts($filter);
        $data['product_total'] = $this->getTotalProducts($filter);

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
        $customerGroupId = $this->getCustomerGroupId();

        /** @var \Alpha\Model\Domain\Repositories\PriceRepository $priceRepo */
        $priceRepo = RepositoryFactory::getInstance()->get(PriceRepository::class);
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

    /**
     * Alpha Engine: Fallback seguro para verificação de login do cliente (Runtime Híbrido)
     */
    private function isCustomerLogged(): bool
    {
        return $this->customer && method_exists($this->customer, 'isLogged') && $this->customer->isLogged();
    }

    /**
     * Alpha Engine: Fallback seguro para resgatar o grupo do cliente (Runtime Híbrido)
     */
    private function getCustomerGroupId(): int
    {
        if ($this->isCustomerLogged()) {
            return (int)$this->customer->getGroupId();
        }
        if (isset($this->configSettings) && isset($this->configSettings['config_customer_group_id'])) {
            return (int)$this->configSettings['config_customer_group_id'];
        }
        if (isset($this->config) && $this->config && method_exists($this->config, 'get')) {
            return (int)$this->config->get('config_customer_group_id');
        }
        return 1;
    }

    /**
     * Alpha Engine: Fallback seguro para resgatar configuração de exibição de preço
     */
    private function shouldShowPrice(): bool
    {
        if ($this->isCustomerLogged()) {
            return true;
        }
        if (isset($this->configSettings) && isset($this->configSettings['config_customer_price'])) {
            return !$this->configSettings['config_customer_price'];
        }
        if (isset($this->config) && $this->config && method_exists($this->config, 'get')) {
            return !$this->config->get('config_customer_price');
        }
        return true;
    }
}
