<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\LayoutMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * LayoutRepository - Autoridade de Domínio para Layouts.
 *
 * Centraliza o acesso aos dados de layouts, utilizando o LayoutMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class LayoutRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Busca um layout pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        return $this->mapperFactory->get(LayoutMapper::class)->findById($id);
    }

    /**
     * Retorna todos os layouts ativos no sistema.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        return $this->mapperFactory->get(LayoutMapper::class)->findAll();
    }

    /**
     * Busca layouts baseado em critérios específicos.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get(LayoutMapper::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca um único layout baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return $this->mapperFactory->get(LayoutMapper::class)->findOneBy($criteria);
    }
}