<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\OrderReturn;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Mappers\EntityMappers\OrderReturnMapper;

/**
 * OrderReturnRepository
 * Centraliza o acesso aos dados das devoluções solicitadas pelos clientes.
 */
class OrderReturnRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): OrderReturnMapper
    {
        return $this->mapperFactory->get(OrderReturnMapper::class);
    }

    /**
     * Busca todo o histórico de devoluções de um cliente específico.
     *
     * @param int $customerId
     * @return OrderReturn[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->getMapper()->search(
            ['customerId' => $customerId],
            ['dateAdded' => 'DESC']
        );
    }

    /**
     * Busca as devoluções atreladas a um pedido específico.
     *
     * @param int $orderId
     * @return OrderReturn[]
     */
    public function findByOrderId(int $orderId): array
    {
        return $this->getMapper()->search(['orderId' => $orderId]);
    }

    /**
     * Legacy Bridge: Adiciona uma nova solicitação de devolução.
     */
    public function addReturn(array $data): void
    {
        $this->getMapper()->addReturnArray(
            $data,
            (int)$this->customer->getId(),
            (int)$this->config->get('config_return_status_id')
        );
    }

    /**
     * Legacy Bridge: Retorna os dados completos de uma devolução.
     */
    public function getReturn(int $return_id): array
    {
        return $this->getMapper()->getReturnArray(
            $return_id,
            (int)$this->customer->getId(),
            (int)$this->config->get('config_language_id')
        );
    }

    /**
     * Legacy Bridge: Lista todas as devoluções do cliente atual.
     */
    public function getReturns(int $start = 0, int $limit = 20): array
    {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 20;
        }

        return $this->getMapper()->getReturnsArray(
            (int)$this->customer->getId(),
            (int)$this->config->get('config_language_id'),
            $start,
            $limit
        );
    }

    /**
     * Legacy Bridge: Conta o total de devoluções atreladas ao cliente.
     */
    public function getTotalReturns(): int
    {
        return $this->getMapper()->getTotalReturnsCount((int)$this->customer->getId());
    }

    /**
     * Legacy Bridge: Retorna o log (histórico) de status de uma devolução específica.
     */
    public function getHistories(int $return_id, int $start = 0, int $limit = 20): array
    {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 20;
        }

        return $this->getMapper()->getHistoriesArray(
            $return_id,
            (int)$this->config->get('config_language_id'),
            $start,
            $limit
        );
    }

    /**
     * Legacy Bridge: Conta o total de atualizações no histórico de uma devolução.
     */
    public function getTotalHistories(int $return_id): int
    {
        return $this->getMapper()->getTotalHistoriesCount($return_id);
    }

    // --- Implementações Obrigatórias da Interface BaseRepositoryInterface ---

    public function find(int $id): ?InterfaceEntity
    {
        return $this->getMapper()->findById($id);
    }

    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $res = $this->getMapper()->search($criteria);
        return $res[0] ?? null;
    }
}
