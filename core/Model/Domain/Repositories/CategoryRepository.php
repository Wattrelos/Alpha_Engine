<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CategoryMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * CategoryRepository - Repositório central para dados de Categorias
 * 
 * Gerencia a lógica de domínio de categorias (vitrine, breadcrumbs, listagens),
 * delegando a interação com o banco para o CategoryMapper.
 */
class CategoryRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Recupera os dados de uma categoria específica baseada no contexto atual.
     *
     * @param int $categoryId
     * @return array|null
     */
    public function getCategory(int $categoryId): ?array
    {
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        
        return $mapper->getCategory($categoryId, $this->language_id, $this->store_id);
    }

    /**
     * Recupera subcategorias de um determinado pai.
     *
     * @param int $parentId (0 para raiz)
     * @return array
     */
    public function getCategories(int $parentId = 0): array
    {
        /** @var CategoryMapper $mapper */
        $mapper = $this->mapperFactory->get(CategoryMapper::class);
        
        return $mapper->getSubCategories($parentId, $this->language_id, $this->store_id);
    }
        /**
     * Alpha Engine: Orquestra a inteligência de negócios e formatação da Categoria.
     * Consolida dados, subcategorias e carrega produtos via Data Mapper.
     */
    public function getCategoryData(int $categoryId, array $filterData): \Alpha\Model\DataTransferObject\ViewResponse
    {
        // 1. Busca os dados da categoria
        $categoryInfo = $this->getCategory($categoryId);

        if (!$categoryInfo) {
            return new \Alpha\Model\DataTransferObject\ViewResponse([]); // Retorna vazio para engatilhar o erro 404
        }

        $data = $categoryInfo;
        
        // 2. SEO e Metadados
        $this->document->setTitle($categoryInfo['meta_title'] ?: $categoryInfo['name']);
        $this->document->setDescription($categoryInfo['meta_description']);
        $this->document->setKeywords($categoryInfo['meta_keyword']);

        // 3. Imagem da Categoria
        if ($categoryInfo['image'] && is_file(DIR_IMAGE . html_entity_decode($categoryInfo['image'], ENT_QUOTES, 'UTF-8'))) {
            $data['thumb'] = $this->registry->get('model_tool_image')->resize($categoryInfo['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
        } else {
            $data['thumb'] = '';
        }

        $data['description'] = html_entity_decode($categoryInfo['description'], ENT_QUOTES, 'UTF-8');

        // 4. Subcategorias
        $data['categories'] = [];
        $subcategories = $this->getCategories($categoryId);
        
        foreach ($subcategories as $result) {
            $data['categories'][] = [
                'name' => $result['name'],
                'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $filterData['path'] . '_' . $result['id'])
            ];
        }

        // 5. Otimização de Produtos via Repositório de Domínio (Fim do N+1 Queries)
        /** @var \Alpha\Model\Domain\Repositories\ProductRepository $productRepository */
        $productRepository = $this->registry->get('alpha_repository_factory')->get(\Alpha\Model\Domain\Repositories\ProductRepository::class);
        
        $productFilter = [
            'filter_category_id' => $categoryId,
            'filter_filter'      => $filterData['filter_filter'],
            'sort'               => $filterData['sort'],
            'order'              => $filterData['order'],
            'start'              => ($filterData['page'] - 1) * $filterData['limit'],
            'limit'              => $filterData['limit']
        ];

        // Delega ao Repositório do Produto a resolução de loja, idioma e descontos
        $data['products'] = $productRepository->getProducts($productFilter);
        $data['product_total'] = $productRepository->getTotalProducts($productFilter);

        // 6. Montagem para o componente visual de Paginação
        $url = '';
        if (isset($filterData['filter_filter'])) $url .= '&filter=' . $filterData['filter_filter'];
        if (isset($filterData['sort'])) $url .= '&sort=' . $filterData['sort'];
        if (isset($filterData['order'])) $url .= '&order=' . $filterData['order'];
        if (isset($filterData['limit'])) $url .= '&limit=' . $filterData['limit'];

        $data['pagination'] = [
            'total' => $data['product_total'],
            'page'  => $filterData['page'],
            'limit' => $filterData['limit'],
            'url'   => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $filterData['path'] . $url . '&page={page}')
        ];

        return new \Alpha\Model\DataTransferObject\ViewResponse($data);
    }


    // Implementações obrigatórias da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}