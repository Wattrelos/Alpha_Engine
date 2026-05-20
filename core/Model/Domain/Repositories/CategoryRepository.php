<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CategoryMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * CategoryRepository - Autoridade de Domínio para Categorias.
 *
 * Centraliza o acesso aos dados de categorias, utilizando o CategoryMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class CategoryRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Busca uma categoria pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        return $this->mapperFactory->get(CategoryMapper::class)->findById($id);
    }

    /**
     * Retorna todas as categorias ativas no sistema.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        return $this->mapperFactory->get(CategoryMapper::class)->findAll();
    }

    /**
     * Busca categorias baseado em critérios específicos.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get(CategoryMapper::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca uma única categoria baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return $this->mapperFactory->get(CategoryMapper::class)->findOneBy($criteria);
    }
}