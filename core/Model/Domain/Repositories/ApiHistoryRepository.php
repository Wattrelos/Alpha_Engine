<?php

namespace Alpha\Model\Domain\Repositories;

/**
 * ApiHistoryRepository
 * Gerencia a extração dos logs de consumo e requisições à API.
 */
class ApiHistoryRepository extends AbstractRepository
{
    /**
     * Retorna o histórico de requisições recentes de uma API, ordenado do mais novo para o mais antigo.
     *
     * @param int $apiId
     * @return array
     */
    public function getRecentHistory(int $apiId): array
    {
        return $this->mapper->search(
            ['apiId' => $apiId],
            ['dateAdded' => 'DESC']
        );
    }
}