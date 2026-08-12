<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CurrencyMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * CurrencyRepository - Autoridade de Domínio para Moedas.
 *
 * Centraliza o acesso aos dados de moedas, utilizando o CurrencyMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class CurrencyRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Busca uma moeda pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        $cacheKey = "currency.id.{$id}";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entity = $this->mapperFactory->get(CurrencyMapper::class)->findById($id);

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Retorna todas as moedas ativas no sistema.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        $cacheKey = "currency.all";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entities = $this->mapperFactory->get(CurrencyMapper::class)->findAll();

        if ($this->cache) {
            $this->cache->set($cacheKey, $entities);
        }

        return $entities;
    }

    /**
     * Busca moedas baseado em critérios específicos.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get(CurrencyMapper::class)->search($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca uma única moeda baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $cacheKey = "currency.query." . md5(serialize($criteria));

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = $this->mapperFactory->get(CurrencyMapper::class)->search($criteria);
        $entity = $results[0] ?? null;

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Valida se um código de moeda existe e está ativo no sistema.
     */
    public function isValid(string $code): bool
    {
        $currencies = $this->mapperFactory->get(CurrencyMapper::class)->findAllActive();
        foreach ($currencies as $currency) {
            if ($currency['code'] === $code) {
                return true;
            }
        }
        return false;
    }
}
