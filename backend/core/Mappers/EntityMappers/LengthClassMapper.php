<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\LengthClass;
use Alpha\Model\Domain\Entities\LengthClassDescription;

/**
 * LengthClassMapper - Gerenciador de persistência para as unidades de medida de comprimento.
 *
 * Alpha Engine:
 * - Padroniza o acesso e hidratação de LengthClass e suas descrições.
 * - Garante a precisão decimal para os valores de conversão.
 */
class LengthClassMapper extends BaseMapper
{
    protected string $tableName = 'length_class';

    /**
     * Alpha Engine: Recupera todas as classes e hidrata as descrições em lote.
     */
    public function findAll(?int $languageId = null): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName(), 'lc');
            
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
                $entity = new LengthClass();
                $entity->setId($id);
                $entity->setValue((float)$row['value']);
                $mapped[$id] = $entity;
                $entities[] = $entity;
            }

            if (!empty($row['title'])) {
                $desc = new LengthClassDescription();
                $desc->setLanguageId((int)$row['language_id']);
                $desc->setTitle($row['title']);
                $desc->setUnit($row['unit']);
                
                if (method_exists($mapped[$id], 'addDescription')) {
                    $mapped[$id]->addDescription($desc);
                } else {
                    // Fallback para a implementação anterior
                    $mapped[$id]->setDescriptions([$desc]);
                }
            }
        }

        return $entities;
    }

    /**
     * Alpha Engine: Recupera uma classe de comprimento pelo ID.
     */
    public function findById(int $id, ?int $languageId = null): ?LengthClass
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
     * Alpha Engine: Recupera uma classe de comprimento baseada em critérios básicos.
     */
    public function findOneBy(array $criteria, ?int $languageId = null): ?LengthClass
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
