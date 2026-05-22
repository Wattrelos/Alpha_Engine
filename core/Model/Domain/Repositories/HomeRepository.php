<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\HomeMapper;
use Alpha\Mappers\EntityMappers\BannerMapper;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Support\Collection;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * Repositório especializado em vitrines e lógica de Home Page
 */
class HomeRepository extends AbstractRepository implements BaseRepositoryInterface {
    
    protected function getMapper() {
        return $this->mapperFactory->get(ProductMapper::class);
    }

    /**
     * Alpha Engine: Consolida os dados fundamentais para a Home Page.
     * Isola a lógica de SEO e metadados que antes ficava no controlador legado.
     */
    public function getHomeData(): Collection
    {
        /** @var HomeMapper $homeMapper */
        $homeMapper = $this->mapperFactory->get(HomeMapper::class);
        $metadata = $homeMapper->getHomeMetadata($this->store_id);

        // --- TESTE REAL-TIME DO LOADCONFIG DA ALPHA ENGINE ---
        // Carrega o arquivo system/config/alpha.php
        $this->loadConfig('alpha');
        // Grava no log de erros do OpenCart (system/storage/logs/error.log) para comprovar a injeção na $this->config global
        $this->registry->get('log')->write('[TESTE ALPHA] ' . $this->config->get('alpha_engine_status'));
        // ------------------------------------------------------

        // Configuração de metadados via Document (Domain Logic)
        $this->document->setTitle($metadata['config_meta_title'] ?? $this->config->get('config_name'));
        $this->document->setDescription($metadata['config_meta_description'] ?? '');
        $this->document->setKeywords($metadata['config_meta_keyword'] ?? '');

        return new Collection([
            'title'       => $this->document->getTitle(),
            'description' => $this->document->getDescription(),
            'keywords'    => $this->document->getKeywords()
        ]);
    }

    /**
     * Recupera produtos em destaque utilizando Batch Loading
     * Resolve o problema de N+1 queries para imagens e preços especiais
     */
    public function getFeatured(array $product_ids, int $limit): array {
        if (empty($product_ids)) return [];
        
        $product_ids = array_slice($product_ids, 0, $limit);
        
        /** @var ProductMapper $productMapper */
        $productMapper = $this->getMapper();

        $customerGroupId = $this->customer->isLogged() 
            ? (int)$this->customer->getGroupId() 
            : (int)$this->config->get('config_customer_group_id');

        // Alpha Engine: Utilizamos o método especializado do Mapper para carregar e normalizar os produtos
        return $productMapper->getProductsByIds($product_ids, $this->language_id, $this->store_id, $customerGroupId);
    }

    public function getLatest(int $limit): array {
        /** @var ProductMapper $productMapper */
        $productMapper = $this->getMapper();

        $filterData = [
            'sort'  => 'p.date_added',
            'order' => 'DESC',
            'start' => 0,
            'limit' => $limit
        ];

        $customerGroupId = $this->customer->isLogged() 
            ? (int)$this->customer->getGroupId() 
            : (int)$this->config->get('config_customer_group_id');

        return $productMapper->getProducts($filterData, $this->language_id, $this->store_id, $customerGroupId);
    }

    /**
     * Ampliação: Método para resolver layouts de banners da Home
     */
    public function getHomeBanners(int $banner_id): ?object {
        $bannerMapper = $this->mapperFactory->get(BannerMapper::class);
        return $bannerMapper->findById($banner_id);
    }

    // Implementações obrigatórias da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}