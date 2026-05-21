<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\WeightClass;
use Alpha\Model\Domain\Entities\WeightClassDescription;

/**
 * Mapper para gerenciar classes de peso (Weight Classes)
 */
class WeightClassMapper extends BaseMapper {
    protected string $tableName = 'weight_class';

    /**
     * Alpha Engine: Recupera todas as classes e hidrata as descrições em lote.
     */
    public function findAll(?int $languageId = null): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName(), 'wc');

        if ($languageId !== null) {
            $query->leftJoin(DB_PREFIX . 'weight_class_description', 'wcd', 'wc.id = wcd.weight_class_id')
                  ->where('wcd.language_id = ?', [$languageId])
                  ->select('wc.id AS id, wc.value, wcd.title, wcd.unit, wcd.language_id');
        } else {
            $query->leftJoin(DB_PREFIX . 'weight_class_description', 'wcd', 'wc.id = wcd.weight_class_id')
                  ->select('wc.id AS id, wc.value, wcd.title, wcd.unit, wcd.language_id');
        }

        $results = $this->dao->executeQuery($query);
        
        $entities = [];
        $mapped = [];

        foreach ($results as $row) {
            $id = (int)$row['id'];

            if (!isset($mapped[$id])) {
                $entity = new WeightClass();
                $entity->setId($id);
                $entity->setValue((float)$row['value']);
                $mapped[$id] = $entity;
                $entities[] = $entity;
            }

            if (!empty($row['title'])) {
                $desc = new WeightClassDescription();
                $desc->setLanguageId((int)$row['language_id']);
                $desc->setTitle($row['title']);
                $desc->setUnit($row['unit']);
                $mapped[$id]->addDescription($desc);
            }
        }
        
        return $entities;
    }

    /**
     * Alpha Engine: Recupera uma classe de peso pelo ID.
     */
    public function findById(int $id, ?int $languageId = null): ?WeightClass
    {
        $all = $this->findAll($languageId);
        foreach ($all as $entity) {
            if ($entity->getId() === $id) {
                return $entity;
            }
        }
        return null;
    }

    /**
     * Alpha Engine: Recupera uma classe de peso baseada em critérios básicos.
     */
    public function findOneBy(array $criteria, ?int $languageId = null): ?WeightClass
    {
        $all = $this->findAll($languageId);
        foreach ($all as $entity) {
            $match = true;
            foreach ($criteria as $key => $value) {
                if ($key === 'id' && $entity->getId() !== $value) {
                    $match = false;
                    break;
                }
                if ($key === 'value' && $entity->getValue() !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) return $entity;
        }
        return null;
    }
}