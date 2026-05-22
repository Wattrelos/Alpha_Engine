<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\EventMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * EventRepository - Autoridade de Domínio para Eventos do Sistema.
 */
class EventRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): EventMapper
    {
        return $this->mapperFactory->get(EventMapper::class);
    }

    /**
     * Recupera todos os eventos ativos.
     */
    public function getEvents(): array
    {
        return $this->getMapper()->getEvents();
    }

    // Métodos obrigatórios da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}