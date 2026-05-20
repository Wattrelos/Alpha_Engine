<?php

namespace Alpha\Repository;

/**
 * BaseRepositoryInterface - Contrato padrão para Repositórios da Alpha Engine.
 * 
 * Define métodos para busca individual e listagens complexas (index).
 */
interface BaseRepositoryInterface
{
    public function find(int $id): ?object;
    
    public function findAll(): array;

    /**
     * Abastece o método index() dos controllers com dados processados.
     * 
     * @param array $filters Filtros vindos do GET/POST
     * @return array Estrutura pronta para ser enviada à View (data + pagination)
     */
    public function getIndexData(array $filters = []): array;
}