<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\LanguageMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * LanguageRepository - Autoridade de Domínio para Idiomas.
 * 
 * Centraliza o acesso aos dados de idiomas, utilizando o LanguageMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class LanguageRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Busca um idioma pelo seu ID único.
     * 
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        $cacheKey = "language.id.{$id}";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entity = $this->mapperFactory->get(LanguageMapper::class)->findById($id);

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Retorna todos os idiomas ativos no sistema.
     * 
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        $cacheKey = "language.all";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entities = $this->mapperFactory->get(LanguageMapper::class)->findAll();

        if ($this->cache) {
            $this->cache->set($cacheKey, $entities);
        }

        return $entities;
    }

    /**
     * Busca idiomas baseado em critérios específicos.
     * 
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get(LanguageMapper::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca um único idioma baseado em critérios.
     * 
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $cacheKey = "language.query." . md5(serialize($criteria));

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entity = $this->mapperFactory->get(LanguageMapper::class)->findOneBy($criteria);

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Busca um idioma pelo seu código ISO (ex: 'pt-br').
     * Essencial para a inicialização do contexto de idioma da loja.
     */
    public function getByCode(string $code): ?InterfaceEntity
    {
        return $this->findOneBy(['code' => $code]);
    }
}