<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar a lógica de Artigos (CMS)
 */
class ArticleMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém um artigo específico
     */
    public function getArticle(int $article_id, int $language_id, int $store_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'article', 'a')
            ->leftJoin(DB_PREFIX . 'article_description', 'ad', 'a.id = ad.id')
            ->leftJoin(DB_PREFIX . 'article_to_store', 'a2s', 'a.id = a2s.article_id')
            ->where("a.id = ?", [$article_id])
            ->where("ad.language_id = ?", [$language_id])
            ->where("a2s.store_id = ?", [$store_id])
            ->where("a.status = ?", [1])
            ->select('DISTINCT *');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista artigos com filtros
     */
    public function getArticles(array $data, int $language_id, int $store_id): array {
        $query = $this->getBaseQuery($data, $language_id, $store_id);

        $query->select('*');

        // Ordenação
        $sort_data = ['rating', 'date_added'];
        $sort = (isset($data['sort']) && in_array($data['sort'], $sort_data)) ? $data['sort'] : 'date_added';
        $order = (isset($data['order']) && $data['order'] == 'DESC') ? 'DESC' : 'ASC';
        
        $query->orderBy("a." . $sort, $order);

        // Paginação
        $limit = (int)($data['limit'] ?? 20);
        $start = (int)($data['start'] ?? 0);
        $query->limit($limit)->offset($start);

        return $this->dao->executeQuery($query);
    }

    /**
     * Conta o total de artigos
     */
    public function getTotalArticles(array $data, int $language_id, int $store_id): int {
        $query = $this->getBaseQuery($data, $language_id, $store_id);
        return $this->dao->executeCount($query);
    }

    /**
     * Atualiza a nota (rating) do artigo
     */
    public function updateRating(int $article_id, int $rating): void {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = "UPDATE `" . DB_PREFIX . "article` SET `rating` = ? WHERE `id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([(int)$rating, (int)$article_id]);
    }

    /**
     * Encapsula a lógica base de filtros para reuso entre Listagem e Contagem
     */
    private function getBaseQuery(array $data, int $language_id, int $store_id): QueryBuilder {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'article', 'a')
            ->leftJoin(DB_PREFIX . 'article_description', 'ad', 'a.id = ad.id')
            ->leftJoin(DB_PREFIX . 'article_to_store', 'a2s', 'a.id = a2s.article_id')
            ->where("ad.language_id = ?", [$language_id])
            ->where("a2s.store_id = ?", [$store_id])
            ->where("a.status = ?", [1]);

        if (!empty($data['filter_search'])) {
            $search = "%" . $data['filter_search'] . "%";
            $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_search'])));
            $words = array_filter($words);

            $subConditions = [];
            $params = [];

            foreach ($words as $word) {
                $subConditions[] = "ad.name LIKE ?";
                $params[] = "%" . $word . "%";
                $subConditions[] = "ad.tag LIKE ?";
                $params[] = "%" . $word . "%";
            }
            $subConditions[] = "ad.description LIKE ?";
            $params[] = $search;

            $query->where("(" . implode(" OR ", $subConditions) . ")", $params);
        }

        if (!empty($data['filter_topic_id'])) {
            $query->where("a.topic_id = ?", [(int)$data['filter_topic_id']]);
        }

        return $query;
    }
}
