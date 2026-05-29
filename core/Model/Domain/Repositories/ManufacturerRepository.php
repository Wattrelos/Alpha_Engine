<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ManufacturerMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * ManufacturerRepository - Autoridade de Domínio para Fabricantes.
 *
 * Centraliza o acesso aos dados de fabricantes, utilizando o ManufacturerMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class ManufacturerRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Alpha Engine: Recupera os dados hidratados de um Fabricante
     * 
     * @param int $manufacturerId
     * @return array|null
     */
    public function getManufacturer(int $manufacturerId): ?array
    {
        /** @var \Alpha\Mappers\EntityMappers\ManufacturerMapper $mapper */
        $mapper = $this->mapperFactory->get(ManufacturerMapper::class);
        return method_exists($mapper, 'getManufacturer') 
            ? $mapper->getManufacturer($manufacturerId, $this->store_id) 
            : null;
    }

    /**
     * Alpha Engine: Agrupa os fabricantes alfabeticamente (A-Z, 0-9)
     *
     * @return array
     */
    public function getAllGrouped(): array
    {
        /** @var \Alpha\Mappers\EntityMappers\ManufacturerMapper $mapper */
        $mapper = $this->mapperFactory->get(ManufacturerMapper::class);
        $results = $mapper->getManufacturers([], $this->store_id);

        $categories = [];

        foreach ($results as $result) {
            if (!empty($result['name'])) {
                $character = mb_substr($result['name'], 0, 1);

                if (is_numeric($character)) {
                    $key = '0 - 9';
                } else {
                    $key = mb_strtoupper($character);
                }

                if (!isset($categories[$key])) {
                    $categories[$key] = [
                        'name'         => $key,
                        'href'         => $this->url->link('product/manufacturer', 'language=' . $this->config->get('config_language')),
                        'manufacturer' => []
                    ];
                }

                $categories[$key]['manufacturer'][] = [
                    'name' => $result['name'],
                    'href' => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $result['id'])
                ];
            }
        }

        ksort($categories);

        return $categories;
    }

    /**
     * Alpha Engine: Prepara o DTO de visualização para a página de detalhes de um fabricante.
     *
     * @param int $manufacturerId
     * @param array $filterData
     * @return \Alpha\Model\DataTransferObject\ViewResponse
     */
    public function getManufacturerData(int $manufacturerId, array $filterData): \Alpha\Model\DataTransferObject\ViewResponse
    {
        $this->loadLanguage('product/manufacturer');

        $manufacturer_info = $this->getManufacturer($manufacturerId);

        if (!$manufacturer_info) {
            return new \Alpha\Model\DataTransferObject\ViewResponse([]);
        }

        $data = [];
        $data['name'] = $manufacturer_info['name'];

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_brand') ?: 'Marcas',
            'href' => $this->url->link('product/manufacturer', 'language=' . $this->config->get('config_language'))
        ];
        $data['breadcrumbs'][] = [
            'text' => $manufacturer_info['name'],
            'href' => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $manufacturerId)
        ];

        /** @var \Alpha\Model\Domain\Repositories\ProductRepository $productRepository */
        $productRepository = $this->registry->get('alpha_repository_factory')->get(\Alpha\Model\Domain\Repositories\ProductRepository::class);

        $productFilter = [
            'filter_manufacturer_id' => $manufacturerId,
            'sort'                   => $filterData['sort'] ?? 'p.sort_order',
            'order'                  => $filterData['order'] ?? 'ASC',
            'start'                  => (($filterData['page'] ?? 1) - 1) * ($filterData['limit'] ?? 10),
            'limit'                  => $filterData['limit'] ?? 10
        ];

        $results = $productRepository->getProducts($productFilter);
        $product_total = $productRepository->getTotalProducts($productFilter);

        $data['products'] = [];
        foreach ($results as $result) {
            $data['products'][] = $this->viewRenderer->render('product/thumb', $productRepository->getProductThumbData($result));
        }

        // URL base para ordenação/limite
        $baseUrl = '&manufacturer_id=' . $manufacturerId;

        // Limites de página
        $data['limits'] = [];
        $limits = array_unique([$this->config->get('config_pagination_catalog') ?: 10, 25, 50, 75, 100]);
        sort($limits);
        foreach ($limits as $value) {
            $data['limits'][] = [
                'text'  => $value,
                'value' => $value,
                'href'  => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . $baseUrl . '&limit=' . $value)
            ];
        }

        // Ordenação
        $urlWithLimit = $baseUrl . '&limit=' . ($filterData['limit'] ?? 10);
        $data['sorts'] = [];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_default') ?: 'Padrão',
            'value' => 'p.sort_order-ASC',
            'href'  => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&sort=p.sort_order&order=ASC' . $urlWithLimit)
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_name_asc') ?: 'Nome (A - Z)',
            'value' => 'pd.name-ASC',
            'href'  => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&sort=pd.name&order=ASC' . $urlWithLimit)
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_name_desc') ?: 'Nome (Z - A)',
            'value' => 'pd.name-DESC',
            'href'  => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&sort=pd.name&order=DESC' . $urlWithLimit)
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_price_asc') ?: 'Preço (Menor > Maior)',
            'value' => 'p.price-ASC',
            'href'  => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&sort=p.price&order=ASC' . $urlWithLimit)
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_price_desc') ?: 'Preço (Maior > Menor)',
            'value' => 'p.price-DESC',
            'href'  => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&sort=p.price&order=DESC' . $urlWithLimit)
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_model_asc') ?: 'Modelo (A - Z)',
            'value' => 'p.model-ASC',
            'href'  => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&sort=p.model&order=ASC' . $urlWithLimit)
        ];
        $data['sorts'][] = [
            'text'  => $this->language->get('text_model_desc') ?: 'Modelo (Z - A)',
            'value' => 'p.model-DESC',
            'href'  => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&sort=p.model&order=DESC' . $urlWithLimit)
        ];

        $data['sort']  = $filterData['sort'] ?? 'p.sort_order';
        $data['order'] = $filterData['order'] ?? 'ASC';
        $data['limit'] = $filterData['limit'] ?? 10;

        // Paginação
        $url = '';
        if (isset($filterData['sort'])) $url .= '&sort=' . $filterData['sort'];
        if (isset($filterData['order'])) $url .= '&order=' . $filterData['order'];
        if (isset($filterData['limit'])) $url .= '&limit=' . $filterData['limit'];

        $paginationRepository = $this->registry->get('alpha_repository_factory')->get(\Alpha\Model\Domain\Repositories\PaginationRepository::class);
        $paginationData = $paginationRepository->prepare([
            'total' => $product_total,
            'page'  => $filterData['page'] ?? 1,
            'limit' => $filterData['limit'] ?? 10,
            'url'   => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $manufacturerId . $url . '&page={page}')
        ]);

        $viewRenderer = new \Alpha\View\ViewRenderer($this->registry);
        $data['pagination'] = $paginationData->shouldRender() 
            ? $viewRenderer->render('common/pagination', $paginationData->toArray()) 
            : '';

        $data['results'] = sprintf(
            $this->language->get('text_pagination'), 
            ($product_total) ? (($filterData['page'] - 1) * $filterData['limit']) + 1 : 0, 
            ((($filterData['page'] - 1) * $filterData['limit']) > ($product_total - $filterData['limit'])) ? $product_total : ((($filterData['page'] - 1) * $filterData['limit']) + $filterData['limit']), 
            $product_total, 
            ceil($product_total / $filterData['limit'])
        );

        return new \Alpha\Model\DataTransferObject\ViewResponse($data);
    }

    /**
     * Busca um fabricante pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        return $this->mapperFactory->get(ManufacturerMapper::class)->findById($id);
    }

    /**
     * Retorna todos os fabricantes ativos no sistema.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        return $this->mapperFactory->get(ManufacturerMapper::class)->findAll();
    }

    /**
     * Busca fabricantes baseado em critérios específicos.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get(ManufacturerMapper::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca um único fabricante baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return $this->mapperFactory->get(ManufacturerMapper::class)->findOneBy($criteria);
    }
}
