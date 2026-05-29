<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\TaxRateMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * TaxRateRepository - Autoridade de Domínio para Taxas de Imposto.
 *
 * Centraliza o acesso aos dados de taxas de imposto, utilizando o TaxRateMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class TaxRateRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Busca uma taxa de imposto pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        $cacheKey = "tax_rate.id.{$id}";

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
     * Retorna todas as taxas de imposto ativas no sistema.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        $cacheKey = "tax_rate.all";

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
     * Busca taxas de imposto baseado em critérios específicos.
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca uma única taxa de imposto baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $cacheKey = "tax_rate.query." . md5(serialize($criteria));

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entity = $this->getMapper()->findOneBy($criteria);

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Define o Mapper principal para a classe (requerido por AbstractRepository).
     */
    protected function getMapper()
    {
        return $this->mapperFactory->get(TaxRateMapper::class);
    }
}
