<?php

namespace Alpha\Mappers;

use Alpha\Model\Domain\InterfaceEntity;

/**
 * MapperInterface - Define o contrato padrão para os Mappers da Alpha Engine.
 * 
 * Padroniza os métodos de acesso e persistência, permitindo que ferramentas
 * genéricas (como scripts de teste ou exportadores) funcionem com qualquer entidade.
 */
interface MapperInterface
{
    /**
     * Localiza uma entidade pelo ID.
     */
    public function findById(int $id): ?InterfaceEntity;

    /**
     * Retorna todas as entidades da tabela.
     */
    public function findAll(): array;

    /**
     * Salva ou atualiza uma entidade.
     */
    public function save(InterfaceEntity $entity): ?int;

    /**
     * Atualiza uma entidade existente.
     */
    public function update(InterfaceEntity $entity): bool;

    /**
     * Remove uma entidade pelo ID.
     */
    public function delete(int $id): bool;

    /**
     * Busca entidades baseada em filtros associativos.
     */
    public function search(array $filters): array;

    /**
     * Busca entidades de forma paginada.
     */
    public function paginate(array $filters, int $page = 1, int $limit = 10, ?array $orderBy = null): array;

    /**
     * Limpa o cache local estático (Identity Map) do ORM.
     * Essencial para rotinas pesadas em lote (Batch/Cron) para prevenir estouro de RAM.
     */
    public function clearIdentityMap(): void;
}