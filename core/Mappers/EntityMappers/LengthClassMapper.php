<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar classes de comprimento (Length Classes)
 */
class LengthClassMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Alpha Engine: Recupera todas as classes e hidrata as descrições em lote.
     */
    public function findAll(?int $languageId = null): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'length_class', 'lc');

        if ($languageId !== null) {
            $query->leftJoin(DB_PREFIX . 'length_class_description', 'lcd', 'lc.id = lcd.length_class_id')
                  ->where('lcd.language_id = ?', [$languageId])
                  ->select('lc.id AS id, lc.value, lcd.title, lcd.unit, lcd.language_id');
        } else {
            $query->leftJoin(DB_PREFIX . 'length_class_description', 'lcd', 'lc.id = lcd.length_class_id')
                  ->select('lc.id AS id, lc.value, lcd.title, lcd.unit, lcd.language_id');
        }

        $results = $this->dao->executeQuery($query);
        
        $entities = [];
        $mapped = [];

        foreach ($results as $row) {
            $id = (int)$row['id'];

            if (!isset($mapped[$id])) {
                $entity = new \Alpha\Model\Domain\Entities\LengthClass();
                $entity->setId($id);
                $entity->setValue((float)$row['value']);
                $mapped[$id] = $entity;
                $entities[] = $entity;
            }

            if (!empty($row['title'])) {
                $desc = new \Alpha\Model\Domain\Entities\LengthClassDescription();
                $desc->setLanguageId((int)$row['language_id']);
                $desc->setTitle($row['title']);
                $desc->setUnit($row['unit']);
                $mapped[$id]->addDescription($desc);
            }
        }
        
        return $entities;
    }

    /**
     * Alpha Engine: Recupera uma classe de comprimento pelo ID.
     */
    public function findById(int $id, ?int $languageId = null): ?\Alpha\Model\Domain\Entities\LengthClass
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
     * Alpha Engine: Recupera uma classe de comprimento baseada em critérios.
     */
    public function findOneBy(array $criteria, ?int $languageId = null): ?\Alpha\Model\Domain\Entities\LengthClass
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