<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\TaxRuleMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * TaxRuleRepository - Autoridade de Domínio para Regras de Imposto.
 *
 * Centraliza o acesso aos dados de regras de imposto, utilizando o TaxRuleMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class TaxRuleRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Busca uma regra de imposto pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        $cacheKey = "tax_rule.id.{$id}";

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
     * Retorna todas as regras de imposto ativas no sistema.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        $cacheKey = "tax_rule.all";

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
     * Busca regras de imposto baseado em critérios específicos.
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca uma única regra de imposto baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $cacheKey = "tax_rule.query." . md5(serialize($criteria));

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
        return $this->mapperFactory->get(TaxRuleMapper::class);
    }
}