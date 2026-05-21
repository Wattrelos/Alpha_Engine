<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\WeightClassMapper;
use Alpha\Model\Domain\Entities\WeightClass;
use Alpha\Support\Collection;

/**
 * WeightClassRepository - Gerencia as unidades de medida de peso.
 * 
 * Implementa cache de longa duração por serem dados de configuração raramente alterados.
 */
class WeightClassRepository extends AbstractRepository implements BaseRepositoryInterface
{
    private const CACHE_KEY_ALL = 'weight_class.all';
    private const CACHE_KEY_PREFIX = 'weight_class.id.';

    protected function getMapper(): WeightClassMapper
    {
        return $this->mapperFactory->get(WeightClassMapper::class);
    }

    /**
     * Busca uma classe de peso pelo ID, priorizando o cache.
     */
    public function find(int $id): ?WeightClass
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $id;
        
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $weightClass = $this->getMapper()->findById($id, $this->language_id);

        if ($weightClass) {
            $this->cache->set($cacheKey, $weightClass, 3600);
        }

        return $weightClass;
    }

    /**
     * Retorna todas as classes de peso configuradas.
     */
    public function findAll(): array
    {
        if ($this->cache->has(self::CACHE_KEY_ALL)) {
            return $this->cache->get(self::CACHE_KEY_ALL);
        }

        $results = $this->getMapper()->findAll();
        
        if (!empty($results)) {
            $this->cache->set(self::CACHE_KEY_ALL, $results, 3600);
        }

        return $results;
    }

    /**
     * Retorna todas as unidades de peso para o idioma atual.
     *
     * @return \Alpha\Model\Domain\InterfaceEntity[]
     */
    public function getAllByCurrentLanguage(): array
    {
        $cacheKey = self::CACHE_KEY_ALL . '.lang.' . $this->language_id;

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = $this->getMapper()->getAll($this->language_id);

        if (!empty($results)) {
            $this->cache->set($cacheKey, $results, 3600);
        }

        return $results;
    }

    /**
     * Busca classes de peso baseado em critérios específicos.
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca uma classe de peso específica por critérios (ex: unit ou title).
     */
    public function findOneBy(array $criteria): ?WeightClass
    {
        // Implementação via Mapper respeitando o Identity Map
        return $this->getMapper()->findOneBy($criteria, $this->language_id);
    }

    /**
     * Realiza a conversão de valores entre diferentes unidades de peso.
     * 
     * @param float $value O valor a ser convertido.
     * @param int $from_weight_class_id ID da unidade de origem.
     * @param int $to_weight_class_id ID da unidade de destino.
     * @return float Valor convertido.
     */
    public function convert(float $value, int $from_weight_class_id, int $to_weight_class_id): float
    {
        if ($from_weight_class_id == $to_weight_class_id) {
            return $value;
        }

        $from = $this->find($from_weight_class_id);
        $to = $this->find($to_weight_class_id);

        if (!$from || !$to) {
            return $value;
        }

        // Cálculo: (Valor / Valor de Referência da Origem) * Valor de Referência do Destino
        // No OpenCart/Alpha, a base é definida pela unidade com value = 1.0000
        $fromValue = $from->getValue() > 0 ? $from->getValue() : 1;
        $toValue = $to->getValue();

        return $value * ($toValue / $fromValue);
    }
}