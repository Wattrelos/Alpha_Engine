<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Review;

/**
 * ReviewMapper - Centraliza a persistência da entidade Review.
 */
class ReviewMapper extends BaseMapper
{
    protected string $entityClass = Review::class;
    protected string $tableName = 'review';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    public function findById(int $id): ?Review
    {
        $review = new Review();
        $review->setId($id);
        $results = $this->dao->read($review);
        return $results[0] ?? null;
    }

    public function getReviewsByProductId(int $productId, int $page = 1, int $limit = 5): array
    {
        $builder = (new QueryBuilder())
            ->from($this->getFullTableName(), 'r')
            ->where('r.product_id = ?', [$productId])
            ->where('r.status = 1')
            ->orderBy('r.date_added', 'DESC');

        $result = $this->dao->paginate($builder, $page, $limit);
        $reviews = [];
        foreach ($result['data'] as $row) {
            $review = $this->findById((int)$row['id']);
            if ($review) $reviews[] = $review;
        }

        return ['data' => $reviews, 'total' => $result['total']];
    }

    public function approve(int $id): bool
    {
        $review = $this->findById($id);
        if (!$review) return false;
        $review->setStatus(true)->setDateModified(date('Y-m-d H:i:s'));
        return $this->save($review) > 0;
    }

    public function getTotalReviewsByProductId(int $productId): int
    {
        $builder = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where('product_id = ?', [$productId])
            ->where('status = 1');
        return $this->dao->executeCount($builder);
    }
}