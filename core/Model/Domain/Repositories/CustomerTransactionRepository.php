<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CustomerTransactionMapper;
use Alpha\Model\Domain\Entities\CustomerTransaction;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * CustomerTransactionRepository
 * Gerencia os fundos/carteira e histórico financeiro interno do cliente.
 */
class CustomerTransactionRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = CustomerTransactionMapper::class;

    protected function getMapper(): CustomerTransactionMapper
    {
        return $this->mapperFactory->get($this->mapperClass);
    }

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
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }

    /**
     * Retorna todo o histórico de transações de um cliente em ordem cronológica (mais recentes primeiro).
     *
     * @param int $customerId
     * @return CustomerTransaction[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->findBy(['customer_id' => $customerId], ['date_added' => 'DESC']);
    }

    /**
     * Retorna as transações formatadas em array para a view.
     * Substitui o método getTransactions legado.
     */
    public function getTransactions(int $customerId, array $data = []): array
    {
        $limit = $data['limit'] ?? null;
        $offset = $data['start'] ?? null;
        $sort = $data['sort'] ?? 'date_added';
        $order = isset($data['order']) && strtoupper($data['order']) === 'ASC' ? 'ASC' : 'DESC';

        $transactions = $this->findBy(['customer_id' => $customerId], [$sort => $order], $limit, $offset);
        
        $result = [];
        foreach ($transactions as $transaction) {
            $arrayData = $transaction->toArray();
            $arrayData['customer_transaction_id'] = $transaction->getId();
            $result[] = $arrayData;
        }

        return $result;
    }

    /**
     * Retorna o total de transações de um cliente.
     */
    public function getTotalTransactions(int $customerId): int
    {
        return count($this->findBy(['customer_id' => $customerId]));
    }

    /**
     * Calcula e retorna o saldo atual disponível na conta do cliente.
     * O cálculo é feito agregando os valores das transações (créditos são positivos, débitos negativos).
     *
     * @param int $customerId
     * @return float
     */
    public function getBalance(int $customerId): float
    {
        $transactions = $this->findByCustomerId($customerId);
        $balance = 0.0;
        foreach ($transactions as $transaction) { 
            $balance += (float)$transaction->getAmount(); 
        }
        return $balance;
    }

    /**
     * Legacy Bridge: Adiciona uma nova transação.
     */
    public function addTransaction(int $customerId, int $orderId, string $description, float $amount): void
    {
        $transaction = new CustomerTransaction();
        $transaction->setCustomerId($customerId)
                    ->setOrderId($orderId)
                    ->setDescription($description)
                    ->setAmount($amount)
                    ->setDateAdded(date('Y-m-d H:i:s'));

        $this->getMapper()->save($transaction);
    }

    /**
     * Legacy Bridge: Deleta transações baseadas no ID do cliente ou do pedido.
     */
    public function deleteTransaction(int $customerId, int $orderId = 0): void
    {
        $criteria = ['customer_id' => $customerId];
        if ($orderId > 0) {
            $criteria['order_id'] = $orderId;
        }

        $transactions = $this->findBy($criteria);
        foreach ($transactions as $transaction) {
            $this->getMapper()->delete($transaction->getId());
        }
    }

    /**
     * Legacy Bridge: Deleta transações por ID do pedido (apenas débitos).
     */
    public function deleteTransactionByOrderId(int $orderId): void
    {
        $transactions = $this->findBy(['order_id' => $orderId]);
        foreach ($transactions as $transaction) {
            if ($transaction->getAmount() < 0) {
                $this->getMapper()->delete($transaction->getId());
            }
        }
    }

    /**
     * Legacy Bridge: Obtém o total de transações de um pedido.
     */
    public function getTotalTransactionsByOrderId(int $orderId): int
    {
        return count($this->findBy(['order_id' => $orderId]));
    }

    /**
     * Legacy Bridge: Retorna o saldo do cliente (Alias para getBalance).
     */
    public function getTransactionTotal(int $customerId): float
    {
        return $this->getBalance($customerId);
    }
}