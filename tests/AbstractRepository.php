<?php

namespace Alpha\Repository;

use Alpha\Mappers\MapperFactory;

/**
 * AbstractRepository - Classe base para implementação de repositórios.
 */
abstract class AbstractRepository implements BaseRepositoryInterface
{
    protected MapperFactory $mapper;
    protected string $entityName;

    public function __construct(MapperFactory $mapper)
    {
        $this->mapper = $mapper;
    }

    public function find(int $id): ?object
    {
        return $this->mapper->get($this->entityName)->findById($id);
    }

    public function findAll(): array
    {
        return $this->mapper->get($this->entityName)->findAll();
    }

    /**
     * Implementação padrão para index. 
     * Pode ser sobrescrito para lógicas complexas de paginação.
     */
    public function getIndexData(array $filters = []): array
    {
        return $this->mapper->get($this->entityName)->findAll($filters);
    }
}