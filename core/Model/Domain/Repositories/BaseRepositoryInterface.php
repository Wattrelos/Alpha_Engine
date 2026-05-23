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

    /**
     * Abastece o método index() dos controllers com dados processados.
     * 
     * @param array $filters Filtros vindos do GET/POST
     * @return array Estrutura pronta para ser enviada à View (data + pagination)
     */
    public function getIndexData(array $filters = []): array;
}