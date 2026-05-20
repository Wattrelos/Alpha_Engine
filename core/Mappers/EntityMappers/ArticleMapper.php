<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar a lógica de Artigos (CMS)
 */
class ArticleMapper extends BaseMapper {

    protected string $tableName = 'article';

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
            ->select('a.*', 'ad.name', 'ad.description', 'ad.meta_title', 'ad.meta_description', 'ad.meta_keyword', 'ad.tag');

        $results = $this->dao->executeQuery($query);
        if (!$results) {
            return [];
        }

        $row = $results[0];
        // Alpha Engine: Normalização para compatibilidade legada
        $row['article_id'] = (int)$row['id'];
        $row['rating'] = (int)($row['rating'] ?? 0);

        return $row;
    }

    /**
     * Lista artigos com filtros
     */
    public function getArticles(array $data, int $language_id, int $store_id): array {
        $query = $this->getBaseQuery($data, $language_id, $store_id);

        $query->select('a.*', 'ad.name', 'ad.description');

        // Ordenação
        $sort_data = ['rating', 'date_added'];
        $sort = (isset($data['sort']) && in_array($data['sort'], $sort_data)) ? $data['sort'] : 'date_added';
        $order = (isset($data['order']) && $data['order'] == 'DESC') ? 'DESC' : 'ASC';

        $query->orderBy("a." . $sort, $order);

        // Paginação
        $limit = (int)($data['limit'] ?? 20);
        $start = (int)($data['start'] ?? 0);
        $query->limit($limit)->offset($start);

        $results = $this->dao->executeQuery($query);

        return array_map(function($row) {
            return ['article_id' => (int)$row['id']] + $row;
        }, $results);
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
     * Obtém o layout associado ao artigo.
     */
    public function getLayoutId(int $article_id, int $store_id): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'article_to_layout')
            ->where("article_id = ?", [$article_id])
            ->where("store_id = ?", [$store_id])
            ->select('layout_id');

        $results = $this->dao->executeQuery($query);
        return $results ? (int)$results[0]['layout_id'] : 0;
    }

    /**
     * Adiciona um comentário ao artigo.
     */
    public function addComment(int $article_id, array $data, int $customer_id, string $ip): int {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = "INSERT INTO `" . DB_PREFIX . "article_comment` SET `article_id` = ?, `parent_id` = ?, `customer_id` = ?, `author` = ?, `comment` = ?, `ip` = ?, `status` = ?, `date_added` = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $article_id,
            (int)($data['parent_id'] ?? 0),
            $customer_id,
            $data['author'],
            $data['comment'],
            $ip,
            (int)!empty($data['status'])
        ]);
        return (int)$conn->lastInsertId();
    }

    /**
     * Edita a nota de um comentário.
     */
    public function editCommentRating(int $article_id, int $article_comment_id, int $rating): void {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = "UPDATE `" . DB_PREFIX . "article_comment` SET `rating` = ? WHERE `article_comment_id` = ? AND `article_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$rating, $article_comment_id, $article_id]);
    }

    /**
     * Recupera um comentário específico.
     */
    public function getComment(int $article_comment_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'article_comment')
            ->where("article_comment_id = ?", [$article_comment_id])
            ->where("status = ?", [1])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Lista comentários com paginação e filtros.
     */
    public function getComments(int $article_id, array $data): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'article_comment')
            ->where("article_id = ?", [$article_id])
            ->where("status = ?", [1]);

        if (!empty($data['customer_id'])) {
            $query->where("customer_id = ?", [(int)$data['customer_id']]);
        }
        if (isset($data['parent_id'])) {
            $query->where("parent_id = ?", [(int)$data['parent_id']]);
        }

        $sort_data = ['rating', 'date_added'];
        $sort = (isset($data['sort']) && in_array($data['sort'], $sort_data)) ? $data['sort'] : 'date_added';
        $order = (isset($data['order']) && $data['order'] == 'DESC') ? 'DESC' : 'ASC';
        $query->orderBy($sort, $order);

        if (isset($data['start']) || isset($data['limit'])) {
            $query->limit((int)($data['limit'] ?? 20))->offset((int)($data['start'] ?? 0));
        }

        return $this->dao->executeQuery($query->select('*'));
    }

    /**
     * Conta o total de comentários filtrados.
     */
    public function getTotalComments(int $article_id, array $data): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'article_comment')
            ->where("article_id = ?", [$article_id])
            ->where("status = ?", [1]);

        if (!empty($data['customer_id'])) {
            $query->where("customer_id = ?", [(int)$data['customer_id']]);
        }
        if (isset($data['parent_id'])) {
            $query->where("parent_id = ?", [(int)$data['parent_id']]);
        }

        return $this->dao->executeCount($query);
    }

    /**
     * Registra uma avaliação detalhada.
     */
    public function addRating(int $article_id, int $article_comment_id, int $store_id, int $customer_id, bool $rating, string $ip): void {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = "INSERT INTO `" . DB_PREFIX . "article_rating` SET `article_comment_id` = ?, `article_id` = ?, `store_id` = ?, `customer_id` = ?, `rating` = ?, `ip` = ?, `date_added` = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$article_comment_id, $article_id, $store_id, $customer_id, (int)$rating, $ip]);
    }

    public function deleteRating(int $article_id, int $article_comment_id, int $customer_id): void {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = "DELETE FROM `" . DB_PREFIX . "article_rating` WHERE `article_comment_id` = ? AND `article_id` = ? AND `customer_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$article_comment_id, $article_id, $customer_id]);
    }

    public function getRatings(int $article_id, int $article_comment_id = 0): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'article_rating')
            ->where("article_id = ?", [$article_id]);

        if ($article_comment_id) {
            $query->where("article_comment_id = ?", [$article_comment_id]);
        }

        $query->groupBy('rating')->select('rating', 'COUNT(*) AS total');
        return $this->dao->executeQuery($query);
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
