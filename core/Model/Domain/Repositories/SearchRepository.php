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
    public function getSearchDisplayData(): ViewResponse
    {
        $response = new ViewResponse();

        $response->set('text_search', $this->language->get('text_search'));
        $response->set('action', $this->url->link('common/search.redirect', 'language=' . $this->config->get('config_language')));
        $response->set('search', $this->request->get['search'] ?? '');

        return $response;
    }

    /**
     * Alpha Engine: Centraliza a construção da URL de busca amigável.
     */
    public function getSearchUrl(string $search): string
    {
        $search = urlencode(html_entity_decode($search, ENT_QUOTES, 'UTF-8'));

        return $this->url->link('product/search', 'language=' . $this->config->get('config_language') . '&search=' . $search, true);
    }

    // Implementações obrigatórias da Interface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}