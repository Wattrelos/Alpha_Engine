<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\InterfaceEntity;

/**
 * Interface padronizada para busca de agregados de domínio
 */
interface BaseRepositoryInterface {
    public function find(int $id): ?InterfaceEntity;
    public function findAll(): array;
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
    public function findOneBy(array $criteria): ?InterfaceEntity;
}