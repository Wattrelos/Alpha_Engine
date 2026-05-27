<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ArticleMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * ArticleRepository - Autoridade de Domínio para Artigos (CMS).
 * 
 * Centraliza as regras de negócio de artigos, comentários e avaliações,
 * substituindo completamente o model legado catalog/model/cms/article.php.
 */
class ArticleRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): ArticleMapper
    {
        return $this->mapperFactory->get(ArticleMapper::class);
    }

    public function getArticle(int $articleId): array
    {
        return $this->getMapper()->getArticle($articleId, $this->language_id, $this->store_id);
    }

    public function getArticles(array $data = []): array
    {
        return $this->getMapper()->getArticles($data, $this->language_id, $this->store_id);
    }

    public function editRating(int $articleId, int $rating): void
    {
        $this->getMapper()->updateRating($articleId, $rating);
    }

    public function getTotalArticles(array $data = []): int
    {
        return $this->getMapper()->getTotalArticles($data, $this->language_id, $this->store_id);
    }

    public function getLayoutId(int $articleId): int
    {
        return $this->getMapper()->getLayoutId($articleId, $this->store_id);
    }

    public function addComment(int $articleId, array $data): int
    {
        $customerId = $this->customer->isLogged() ? (int)$this->customer->getId() : 0;

        $lastId = $this->getMapper()->addComment($articleId, $data, $customerId, oc_get_ip());

        $this->cache->delete('article.comment');
        
        return $lastId;
    }

    public function editCommentRating(int $articleId, int $commentId, int $rating): void
    {
        $this->getMapper()->editCommentRating($articleId, $commentId, $rating);
    }

    public function getComment(int $commentId): array
    {
        return $this->getMapper()->getComment($commentId);
    }

    public function getComments(int $articleId, array $data = []): array
    {
        $key = md5(json_encode([$articleId, $data]));
        $cacheKey = 'article.comment.' . $key;
        
        $commentData = $this->cache->get($cacheKey);

        if (!$commentData) {
            $commentData = $this->getMapper()->getComments($articleId, $data);
            $this->cache->set($cacheKey, $commentData);
        }

        return $commentData;
    }

    public function getTotalComments(int $articleId, array $data = []): int
    {
        return $this->getMapper()->getTotalComments($articleId, $data);
    }

    public function addRating(int $articleId, int $commentId, bool $rating): void
    {
        $customerId = $this->customer->isLogged() ? (int)$this->customer->getId() : 0;
        $this->getMapper()->addRating($articleId, $commentId, $this->store_id, $customerId, $rating, oc_get_ip());
    }

    public function deleteRating(int $articleId, int $commentId): void
    {
        $customerId = $this->customer->isLogged() ? (int)$this->customer->getId() : 0;
        $this->getMapper()->deleteRating($articleId, $commentId, $customerId);
    }

    public function getRatings(int $articleId, int $commentId = 0): array
    {
        return $this->getMapper()->getRatings($articleId, $commentId);
    }

    // BaseRepositoryInterface bindings
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}