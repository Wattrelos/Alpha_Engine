<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\ReturnHistory;

/**
 * ReturnHistoryRepository
 * Gerencia as trilhas de auditoria das devoluções.
 */
class ReturnHistoryRepository extends AbstractRepository
{
    protected function getMapper(): \Alpha\Mappers\EntityMappers\ReturnHistoryMapper
    {
        return $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\ReturnHistoryMapper::class);
    }

    /**
     * Busca todo o histórico cronológico de uma devolução específica.
     *
     * @param int $returnId
     * @return ReturnHistory[]
     */
    public function findByReturnId(int $returnId): array
    {
        // Busca e ordena por data de adição ascendente (do mais antigo pro mais recente)
        return $this->getMapper()->search(
            ['returnId' => $returnId],
            ['dateAdded' => 'ASC']
        );
    }

    public function save(\Alpha\Model\Domain\InterfaceEntity $entity): ?int
    {
        return $this->getMapper()->save($entity);
    }
}
