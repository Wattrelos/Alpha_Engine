<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\StartupMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * StartupRepository - Autoridade de Domínio para Tarefas de Inicialização (Startup).
 */
class StartupRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): StartupMapper
    {
        return $this->mapperFactory->get(StartupMapper::class);
    }

    /**
     * Recupera todas as tarefas de inicialização ativas.
     */
    public function getStartups(): array
    {
        return $this->getMapper()->getStartups();
    }

    // Métodos obrigatórios da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}