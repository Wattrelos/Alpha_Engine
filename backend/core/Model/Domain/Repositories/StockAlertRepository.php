<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\StockAlertMapper;
use Alpha\Model\Domain\Entities\StockAlert;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * StockAlertRepository - Encapsula a lógica de domínio para alertas de estoque.
 * 
 * Conforme ADR 0008 e ADR 0005.
 */
class StockAlertRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = StockAlertMapper::class;

    public function find(int $id): ?InterfaceEntity
    {
        /** @var StockAlertMapper $mapper */
        $mapper = $this->mapperFactory->get(StockAlertMapper::class);
        return $mapper->find($id);
    }

    public function findAll(): array
    {
        /** @var StockAlertMapper $mapper */
        $mapper = $this->mapperFactory->get(StockAlertMapper::class);
        return $mapper->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        /** @var StockAlertMapper $mapper */
        $mapper = $this->mapperFactory->get(StockAlertMapper::class);
        return $mapper->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        /** @var StockAlertMapper $mapper */
        $mapper = $this->mapperFactory->get(StockAlertMapper::class);
        return $mapper->findOneBy($criteria);
    }

    /**
     * Registra ou renova uma inscrição de alerta de estoque.
     *
     * @param array $payload Dados da requisição
     * @return int ID do registro criado ou atualizado
     */
    public function subscribe(array $payload): int
    {
        /** @var StockAlertMapper $mapper */
        $mapper = $this->mapperFactory->get(StockAlertMapper::class);
        
        $data = [
            'store_id'          => (int)($payload['store_id'] ?? $this->getStoreId()),
            'language_id'       => (int)($payload['language_id'] ?? $this->getLanguageId()),
            'product_id'        => (int)$payload['product_id'],
            'variant_id'        => !empty($payload['variant_id']) ? (int)$payload['variant_id'] : null,
            'customer_id'       => !empty($payload['customer_id']) ? (int)$payload['customer_id'] : null,
            'name'              => trim($payload['name'] ?? ''),
            'email'             => trim(strtolower($payload['email'] ?? '')),
            'phone'             => !empty($payload['phone']) ? trim($payload['phone']) : null,
            'ip'                => trim($payload['ip'] ?? '127.0.0.1'),
            'user_agent'        => !empty($payload['user_agent']) ? trim($payload['user_agent']) : null,
            'consent_privacy'   => !empty($payload['consent_privacy']) ? 1 : 1,
            'consent_marketing' => !empty($payload['consent_marketing']) ? 1 : 0,
        ];

        return $mapper->subscribe($data);
    }

    /**
     * Realiza o opt-out (cancelamento) do alerta pelo token recebido por e-mail.
     *
     * @param string $token
     * @return bool
     */
    public function unsubscribe(string $token): bool
    {
        /** @var StockAlertMapper $mapper */
        $mapper = $this->mapperFactory->get(StockAlertMapper::class);
        return $mapper->unsubscribeByToken($token);
    }

    /**
     * Busca os clientes pendentes de notificação aplicando a regra de cota FIFO (Anti-frustração).
     *
     * @param int $productId
     * @param int|null $variantId
     * @param int $replenishedQuantity Quantidade física adicionada ao estoque
     * @param float $quotaMultiplier Fator multiplicador de lote (padrão: 3)
     * @return array
     */
    public function getPendingAlertsForReplenishment(
        int $productId,
        ?int $variantId = null,
        int $replenishedQuantity = 1,
        float $quotaMultiplier = 3.0
    ): array {
        /** @var StockAlertMapper $mapper */
        $mapper = $this->mapperFactory->get(StockAlertMapper::class);

        $limit = max(1, (int)round($replenishedQuantity * $quotaMultiplier));
        return $mapper->getPendingAlertsForReplenishment($this->getStoreId(), $productId, $variantId, $limit);
    }

    /**
     * Marca uma lista de IDs como notificados.
     *
     * @param array $ids
     */
    public function markAsSent(array $ids): void
    {
        /** @var StockAlertMapper $mapper */
        $mapper = $this->mapperFactory->get(StockAlertMapper::class);
        $mapper->markAsSent($ids);
    }
}
