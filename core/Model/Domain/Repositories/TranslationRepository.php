<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\TranslationMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * TranslationRepository - Gerencia traduções dinâmicas do banco de dados (Substitui design/translation).
 */
class TranslationRepository extends AbstractRepository implements BaseRepositoryInterface
{
    private array $loadedTranslations = [];

    /**
     * Alpha Engine: Recupera as traduções do banco de dados para a rota e contexto atual.
     * Utiliza cache em memória para evitar queries N+1 durante eventos da mesma página.
     */
    public function getTranslations(string $route): array
    {
        $key = "{$this->store_id}.{$this->language_id}.{$route}";

        if (isset($this->loadedTranslations[$key])) {
            return $this->loadedTranslations[$key];
        }

        /** @var TranslationMapper $mapper */
        $mapper = $this->mapperFactory->get(TranslationMapper::class);
        $results = $mapper->getRouteTranslations($route, $this->store_id, $this->language_id);

        $this->loadedTranslations[$key] = $results;

        return $results;
    }

    // Implementações obrigatórias da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}