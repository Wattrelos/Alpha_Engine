<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\DataTransferObject\ViewResponse;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * SearchRepository - Gerencia a lógica de busca e dados para o componente de busca.
 */
class SearchRepository extends AbstractRepository
{
    /**
     * Recupera os dados necessários para o componente de busca na interface.
     */
    public function getSearchDisplayData(string $currentSearch = ''): ViewResponse
    {
        $response = new ViewResponse();
        $response->set('search', $currentSearch);
        return $response;
    }

    // Implementações obrigatórias da Interface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}
