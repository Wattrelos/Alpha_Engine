<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar a lógica de Tópicos (CMS)
 */
class TopicMapper extends BaseMapper {

    protected string $tableName = 'topic';

    /**
     * Obtém um tópico específico
     */
    public function getTopic(int $topic_id, int $language_id, int $store_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'topic', 't')
            ->leftJoin(DB_PREFIX . 'topic_description', 'td', 't.id = td.topic_id')
            ->leftJoin(DB_PREFIX . 'topic_to_store', 't2s', 't.id = t2s.topic_id')
            ->where("t.id = ?", [$topic_id])
            ->where("td.language_id = ?", [$language_id])
            ->where("t2s.store_id = ?", [$store_id])
            ->where("t.status = ?", [1])
            ->select('DISTINCT *');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista todos os tópicos ativos para a loja e idioma
     */
    public function getTopics(int $language_id, int $store_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'topic', 't')
            ->leftJoin(DB_PREFIX . 'topic_description', 'td', 't.id = td.topic_id')
            ->leftJoin(DB_PREFIX . 'topic_to_store', 't2s', 't.id = t2s.topic_id')
            ->where("td.language_id = ?", [$language_id])
            ->where("t2s.store_id = ?", [$store_id])
            ->where("t.status = ?", [1])
            ->orderBy("t.sort_order", "DESC")
            ->select('*');

        return $this->dao->executeQuery($query);
    }

    /**
     * Obtém o layout associado ao tópico
     */
    public function getLayoutId(int $topic_id, int $store_id): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'topic_to_layout')
            ->where("topic_id = ?", [$topic_id])
            ->where("store_id = ?", [$store_id])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        return $results ? (int)$results[0]['layout_id'] : 0;
    }
}
