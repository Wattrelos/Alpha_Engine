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