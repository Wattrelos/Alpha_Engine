<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\LengthClassMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * LengthClassRepository - Autoridade de Domínio para Unidades de Medida de Comprimento.
 *
 * Centraliza o acesso às unidades de medida (cm, mm, inch, etc), utilizando o 
 * LengthClassMapper para persistência e garantindo que o Identity Map seja respeitado.
 */
class LengthClassRepository extends AbstractRepository implements BaseRepositoryInterface
{
    private const CACHE_KEY_ALL = 'length_class.all';
    private const CACHE_KEY_PREFIX = 'length_class.id.';

    /**
     * Busca uma unidade de comprimento pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $id;
        
        $cached = $this->cache ? $this->cache->get($cacheKey) : null;
        if ($cached) return $cached;

        $entity = $this->getMapper()->findById($id);
        if ($entity && $this->cache) $this->cache->set($cacheKey, $entity, 3600);
        return $entity;
    }

    /**
     * Retorna todas as unidades de comprimento cadastradas.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        $cacheKey = self::CACHE_KEY_ALL;
        
        $cached = $this->cache ? $this->cache->get($cacheKey) : null;
        if ($cached) return $cached;

        $entities = $this->getMapper()->findAll();
        if (!empty($entities) && $this->cache) $this->cache->set($cacheKey, $entities, 3600);
        return $entities;
    }

    /**
     * Retorna todas as unidades de comprimento para o idioma atual.
     *
     * @return InterfaceEntity[]
     */
    public function getAllByCurrentLanguage(): array
    {
        $cacheKey = self::CACHE_KEY_ALL . '.lang.' . $this->language_id;

        $cached = $this->cache ? $this->cache->get($cacheKey) : null;
        if ($cached) return $cached;

        $results = $this->getMapper()->findAll($this->language_id);
        if (!empty($results) && $this->cache) $this->cache->set($cacheKey, $results, 3600);
        return $results;
    }

    /**
     * Busca unidades baseado em critérios específicos.
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca uma única unidade baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return $this->getMapper()->findOneBy($criteria);
    }

    /**
     * Realiza a conversão de valores entre diferentes unidades de comprimento.
     * 
     * @param float $value O valor a ser convertido.
     * @param int $from_length_class_id ID da unidade de origem.
     * @param int $to_length_class_id ID da unidade de destino.
     * @return float Valor convertido.
     */
    public function convert(float $value, int $from_length_class_id, int $to_length_class_id): float
    {
        if ($from_length_class_id == $to_length_class_id) {
            return $value;
        }

        $from = $this->find($from_length_class_id);
        $to = $this->find($to_length_class_id);

        if (!$from || !$to) {
            return $value;
        }

        $fromValue = $from->getValue() > 0 ? $from->getValue() : 1;
        $toValue = $to->getValue();

        return $value * ($toValue / $fromValue);
    }

    /**
     * Define o Mapper principal para a classe (requerido por AbstractRepository).
     */
    protected function getMapper()
    {
        return $this->mapperFactory->get(LengthClassMapper::class);
    }
}
