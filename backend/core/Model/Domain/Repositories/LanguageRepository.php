<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\LanguageMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Entities\Language;
use Alpha\Mappers\CollectionToArrayConverter;

/**
 * LanguageRepository - Autoridade de Domínio para Idiomas.
 * 
 * Centraliza o acesso aos dados de idiomas, utilizando o LanguageMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class LanguageRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Define o Mapper principal.
     */
    protected function getMapper(): LanguageMapper
    {
        return $this->mapperFactory->get(LanguageMapper::class);
    }

    /**
     * Busca um idioma pelo seu ID único.
     * 
     * @param int $id
     * @return Language|null
     */
    public function find(int $id): ?Language
    {
        $cacheKey = "language.id.{$id}";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entity = $this->getMapper()->findById($id);

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

        $entities = $this->getMapper()->findAll();

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
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca um único idioma baseado em critérios.
     * 
     * @param array $criteria
     * @return Language|null
     */
    public function findOneBy(array $criteria): ?Language
    {
        $cacheKey = "language.query." . md5(serialize($criteria));

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = $this->getMapper()->search($criteria);
        $entity = $results[0] ?? null;

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Busca um idioma pelo seu código ISO (ex: 'pt-br').
     * Essencial para a inicialização do contexto de idioma da loja.
     * 
     * @param string $code
     * @return Language|null
     */
    public function getByCode(string $code): ?Language
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * Alpha Engine: Retorna todos os idiomas ativos em formato de array, 
     * indexados pelo código (ex: 'pt-br').
     * Método crucial para substituir model_localisation_language->getLanguages()
     */
    public function getLanguages(): array
    {
        $cacheKey = "language.array.all";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $languages = [];
        foreach ($this->findAll() as $entity) {
            $arrayData = CollectionToArrayConverter::convertEntity($entity);
            $code = $arrayData['code'] ?? (string)$entity->getId();
            $languages[$code] = $arrayData;
        }

        if ($this->cache) {
            $this->cache->set($cacheKey, $languages);
        }

        return $languages;
    }

    /**
     * Valida se um código de idioma existe no sistema.
     */
    public function isValid(string $code): bool
    {
        return array_key_exists($code, $this->getLanguages());
    }
}
