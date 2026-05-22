<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Order;
use Alpha\Model\Domain\Entities\OrderHistory;
use Alpha\Model\Domain\Entities\OrderProduct;
use Alpha\Model\Domain\Entities\OrderTotal;
use Alpha\Model\Domain\Entities\OrderVoucher;
use Alpha\Model\Domain\Observers\OrderObserverInterface;

/**
 * OrderMapper - Orquestrador de persistência para o ciclo de vida de pedidos.
 * 
 * Melhoras Alpha Engine:
 * - Persistência Atômica: Salva Order e suas coleções filhas em uma única transação via DAO.
 * - Hidratação Recursiva: Recupera o pedido completo com produtos, totais e histórico.
 * - Integridade Financeira: Garante que cálculos de impostos e totais sejam preservados como objetos tipados.
 * - Suporte a Observers: Permite disparar ações automáticas (como e-mails) após o salvamento.
 */
class OrderMapper
{
    private DataAccessObject $dao;
    /** @var OrderObserverInterface[] */
    private array $observers = [];

    public function __construct()
    {
        $this->dao = new DataAccessObject();
    }

    /**
     * Registra um novo observador de pedidos.
     */
    public function attach(OrderObserverInterface $observer): self
    {
        $this->observers[] = $observer;
        return $this;
    }

    /**
     * Salva ou atualiza um pedido completo.
     * O DAO processará automaticamente as coleções (products, totals, vouchers) 
     * através dos atributos #[OneToMany].
     */
    public function save(Order $order): ?int
    {
        $orderId = ($order->getId() > 0) ? $this->dao->update($order) : $this->dao->create($order);

        if ($orderId) {
            $order->setId($orderId);
            $this->notify($order);
        }

        return $orderId;
    }

    private function notify(Order $order): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($order);
        }
    }

    /**
     * Recupera um pedido totalmente hidratado pelo ID.
     */
    public function getOrder(int $orderId): ?Order
    {
        $order = new Order();
        $order->setId($orderId);

        $results = $this->dao->read($order);
        
        return $results ? $results[0] : null;
    }

    /**
     * Adiciona uma entrada no histórico do pedido.
     */
    public function addHistory(int $orderId, int $orderStatusId, string $comment = '', bool $notify = false): void
    {
        $history = new OrderHistory();
        $history->setOrderId($orderId)
            ->setOrderStatusId($orderStatusId)
            ->setComment($comment)
            ->setNotify($notify)
            ->setDateAdded(date('Y-m-d H:i:s'));

        $this->dao->create($history);
    }

    /**
     * Exclui um pedido e todas as suas dependências em cascata.
     */
    public function delete(int $orderId): bool
    {
        $order = new Order();
        $order->setId($orderId);
        
        return (bool)$this->dao->delete($order);
    }

    /**
     * Busca uma lista de pedidos com filtros básicos.
     * @return Order[]
     */
    public function getOrders(array $filterData = [], int $page = 1, int $limit = 20): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'order', 'o')
            ->orderBy('o.date_added', 'DESC');

        if (!empty($filterData['customer_id'])) {
            $builder->where('o.customer_id = ?', [(int)$filterData['customer_id']]);
        }

        if (!empty($filterData['order_status_id'])) {
            $builder->where('o.order_status_id = ?', [(int)$filterData['order_status_id']]);
        }

        $pagination = $this->dao->paginate($builder, $page, $limit);

        // Alpha Engine Optimization: Resolve a hidratação de todos os pedidos em lote, evitando N+1 queries.
        $ids = array_column($pagination['data'], 'id');
        return !empty($ids) ? $this->dao->readByIds(Order::class, array_map('intval', $ids)) : [];
    }
}