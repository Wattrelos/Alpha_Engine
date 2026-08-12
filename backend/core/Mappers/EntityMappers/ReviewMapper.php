<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Review;

/**
 * ReviewMapper - Centraliza a persistência e as consultas complexas da entidade Review.
 * 
 * Seguindo o padrão Alpha Engine:
 * - Utiliza DataAccessObject para hidratação recursiva (Eager Loading).
 * - QueryBuilder para filtros dinâmicos e paginação segura.
 * - Interface fluida para os Controllers de Catálogo e Administração.
 */
class ReviewMapper
{
    private DataAccessObject $dao;

    public function __construct()
    {
        $this->dao = new DataAccessObject();
    }

    /**
     * Persiste ou atualiza uma avaliação no banco de dados.
     * Graças ao DAO, se o ID for nulo, ele executa INSERT; se existir, UPDATE.
     */
    public function save(Review $review): ?int
    {
        if ($review->getId() > 0) {
            return $this->dao->update($review);
        }
        return $this->dao->create($review);
    }

    /**
     * Remove uma avaliação permanentemente.
     */
    public function delete(int $id): bool
    {
        $review = new Review();
        $review->setId($id);
        return (bool)$this->dao->delete($review);
    }

    /**
     * Localiza uma avaliação pelo ID. 
     * O DAO preencherá automaticamente o objeto Product e Customer através da reflexão de atributos.
     */
    public function findById(int $id): ?Review
    {
        $review = new Review();
        $review->setId($id);

        $results = $this->dao->read($review);
        return $results[0] ?? null;
    }

    /**
     * Busca avaliações aprovadas de um produto com suporte a paginação.
     * 
     * @return array{data: Review[], total: int}
     */
    public function getReviewsByProductId(int $productId, int $page = 1, int $limit = 5): array
    {
        $builder = new QueryBuilder();
        $builder->from('review', 'r')
            ->where('r.product_id = :product_id', [':product_id' => $productId])
            ->where('r.status = 1')
            ->orderBy('r.date_added', 'DESC');

        // O DataAccessObject->paginate retorna os dados brutos e o total absoluto
        $result = $this->dao->paginate($builder, $page, $limit);

        $reviews = [];
        foreach ($result['data'] as $row) {
            // Utilizamos o findById para garantir a hidratação completa das entidades vinculadas
            $review = $this->findById((int)$row['id']);
            if ($review) {
                $reviews[] = $review;
            }
        }

        return [
            'data'  => $reviews,
            'total' => $result['total']
        ];
    }

    /**
     * Aprova uma avaliação pendente e atualiza a data de modificação.
     */
    public function approve(int $id): bool
    {
        $review = $this->findById($id);
        
        if (!$review) {
            return false;
        }

        $review->setStatus(true);
        $review->setDateModified(date('Y-m-d H:i:s'));
        
        return $this->save($review) > 0;
    }

    /**
     * Retorna o total de avaliações aprovadas para um produto.
     */
    public function getTotalReviewsByProductId(int $productId): int
    {
        $builder = (new QueryBuilder())
            ->from('review')
            ->where('product_id = ?', [$productId])
            ->where('status = 1');
        return $this->dao->executeCount($builder);
    }
}