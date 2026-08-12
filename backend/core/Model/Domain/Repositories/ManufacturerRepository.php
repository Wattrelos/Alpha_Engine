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
     * Alpha Engine: Recupera a lista de fabricantes/marcas ativos no sistema
     *
     * @param array $data Filtros/ordenamento
     * @return array
     */
    public function getManufacturers(array $data = []): array
    {
        /** @var \Alpha\Mappers\EntityMappers\ManufacturerMapper $mapper */
        $mapper = $this->mapperFactory->get(ManufacturerMapper::class);
        return $mapper->getManufacturers($data, $this->store_id);
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
                        'manufacturer' => []
                    ];
                }

                $categories[$key]['manufacturer'][] = [
                    'id'   => (int)$result['id'],
                    'name' => $result['name']
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
        $manufacturer_info = $this->getManufacturer($manufacturerId);

        if (!$manufacturer_info) {
            return new \Alpha\Model\DataTransferObject\ViewResponse([]);
        }

        $data = $manufacturer_info;
        $data['name'] = $manufacturer_info['name'];

        /** @var \Alpha\Model\Domain\Repositories\ProductRepository $productRepository */
        $productRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\ProductRepository::class);

        $productFilter = [
            'filter_manufacturer_id' => $manufacturerId,
            'sort'                   => $filterData['sort'] ?? 'p.sort_order',
            'order'                  => $filterData['order'] ?? 'ASC',
            'start'                  => (($filterData['page'] ?? 1) - 1) * ($filterData['limit'] ?? 10),
            'limit'                  => $filterData['limit'] ?? 10
        ];

        $data['products'] = $productRepository->getProducts($productFilter);
        $data['product_total'] = $productRepository->getTotalProducts($productFilter);
        
        $data['sort']  = $filterData['sort'] ?? 'p.sort_order';
        $data['order'] = $filterData['order'] ?? 'ASC';
        $data['limit'] = $filterData['limit'] ?? 10;
        $data['page']  = $filterData['page'] ?? 1;

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

    /**
     * Alpha Engine: Recupera fabricantes associados aos produtos de uma categoria ou subcategorias
     *
     * @param int $categoryId
     * @return array
     */
    /**
     * Alpha Engine: Recupera fabricantes associados aos produtos de uma categoria ou subcategorias
     *
     * @param int $categoryId
     * @return array
     */
    public function getManufacturersByCategory(int $categoryId): array
    {
        /** @var \Alpha\Mappers\EntityMappers\ManufacturerMapper $mapper */
        $mapper = $this->mapperFactory->get(ManufacturerMapper::class);
        return $mapper->getManufacturersByCategory($categoryId, $this->store_id);
    }

    /**
     * Retorna a listagem de fabricantes filtrada e paginada para o admin.
     */
    public function getManufacturersPaginated(array $filters, int $page = 1, int $limit = 15, ?int $storeId = null): array
    {
        $sId = $storeId ?? $this->store_id;
        /** @var ManufacturerMapper $mapper */
        $mapper = $this->mapperFactory->get(ManufacturerMapper::class);
        return $mapper->getAdminManufacturersPaginated($filters, $page, $limit, $sId);
    }

    /**
     * Busca um fabricante para edição no admin.
     */
    public function getManufacturerForEdit(int $manufacturerId, ?int $storeId = null, ?int $languageId = null): ?array
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;
        /** @var ManufacturerMapper $mapper */
        $mapper = $this->mapperFactory->get(ManufacturerMapper::class);
        return $mapper->getAdminManufacturerForEdit($manufacturerId, $sId, $lId);
    }

    /**
     * Cria um novo fabricante e invalida o cache.
     */
    public function createManufacturer(array $data, ?int $storeId = null, ?int $languageId = null): int
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;
        /** @var ManufacturerMapper $mapper */
        $mapper = $this->mapperFactory->get(ManufacturerMapper::class);

        $manufacturerId = $mapper->createManufacturer($data, $sId, $lId);
        $this->clearManufacturerCaches($manufacturerId, $sId, $lId);

        return $manufacturerId;
    }

    /**
     * Atualiza um fabricante e invalida o cache.
     */
    public function updateManufacturer(int $manufacturerId, array $data, ?int $storeId = null, ?int $languageId = null): bool
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;
        /** @var ManufacturerMapper $mapper */
        $mapper = $this->mapperFactory->get(ManufacturerMapper::class);

        $success = $mapper->updateManufacturer($manufacturerId, $data, $sId, $lId);
        if ($success) {
            $this->clearManufacturerCaches($manufacturerId, $sId, $lId);
        }

        return $success;
    }

    /**
     * Exclui um fabricante e invalida o cache.
     */
    public function deleteManufacturer(int $manufacturerId, ?int $storeId = null, ?int $languageId = null): bool
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;
        /** @var ManufacturerMapper $mapper */
        $mapper = $this->mapperFactory->get(ManufacturerMapper::class);

        $success = $mapper->deleteManufacturer($manufacturerId);
        if ($success) {
            $this->clearManufacturerCaches($manufacturerId, $sId, $lId);
        }

        return $success;
    }

    /**
     * Limpa o cache de fabricantes.
     */
    public function clearManufacturerCaches(int $manufacturerId, ?int $storeId = null, ?int $languageId = null): void
    {
        $sId = $storeId ?? $this->store_id;
        $lId = $languageId ?? $this->language_id;

        if ($this->cache) {
            $this->cache->delete("manufacturer.{$manufacturerId}.{$lId}.{$sId}");
        }
    }
}
