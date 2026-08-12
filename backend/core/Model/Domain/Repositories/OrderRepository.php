<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\DTOs\OrderDataDTO;
use Alpha\Mappers\EntityMappers\OrderMapper;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Mappers\MapperFactory;
use Containers\AppContainer;
use Alpha\Model\Domain\Repositories\CouponRepository;

/**
 * Class OrderRepository
 * 
 * Gerencia a lógica de negócio e orquestração de persistência de pedidos.
 */
class OrderRepository extends AbstractRepository implements BaseRepositoryInterface {

    private UnitOfWork $unitOfWork;

    /**
     * Construtor da Alpha Engine.
     */
    public function __construct(
        MapperFactory $mapperFactory,
        ?AppContainer $container = null,
        ?\Alpha\Support\Cache\CacheStrategyInterface $cache = null
    ) {
        parent::__construct($mapperFactory, $container, $cache);
        $this->unitOfWork = new UnitOfWork();
        $this->mapperClass = OrderMapper::class;
    }

    /**
     * Obtém o Mapper de pedidos.
     */
    protected function getMapper(): OrderMapper
    {
        return $this->mapperFactory->get(OrderMapper::class);
    }

    /**
     * Centraliza o processamento final da venda (Pós-Pagamento).
     * 
     * Alpha Engine: Garante que a transição de status e a baixa de estoque 
     * sejam executadas de forma atômica e irreversível.
     * 
     * @param int $orderId
     * @param int $orderStatusId
     * @param string $comment
     * @param bool $notify
     */
    public function confirm(int $orderId, int $orderStatusId, string $comment = '', bool $notify = false): void {
        $this->unitOfWork->transaction(function() use ($orderId, $orderStatusId, $comment, $notify) {
            /** @var OrderMapper $mapper */
            $mapper = $this->getMapper();
            
            // Alpha Engine: addHistory já gerencia internamente a baixa de estoque 
            // se o status for processamento ou completo.
            $mapper->addHistory($orderId, $orderStatusId, $comment, $notify);
        });
    }

    /**
     * Persiste o DTO de pedido no banco de dados através do Mapper.
     */
    public function save(OrderDataDTO $dto): int {
        /** @var OrderMapper $mapper */
        $mapper = $this->getMapper();
        
        return $mapper->insert($dto);
    }

    // --- Legacy Bridges (account/order) ---

    public function getOrder(int $order_id, int $customer_id = 0): array {
        return $this->getMapper()->getOrderArray($order_id, $customer_id, $this->store_id);
    }

    public function getOrders(int $customer_id, int $start = 0, int $limit = 20): array {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 20;
        }
        return $this->getMapper()->getOrdersArray(
            $customer_id,
            $this->store_id,
            $this->language_id,
            $start,
            $limit
        );
    }

    public function getTotalOrders(int $customer_id): int {
        return $this->getMapper()->getTotalOrdersCount(
            $customer_id,
            $this->store_id
        );
    }

    public function getTotalProductsByOrderId(int $order_id): int {
        return $this->getMapper()->getTotalProductsByOrderId($order_id);
    }

    public function getProducts(int $order_id): array {
        return $this->getMapper()->getProductsArray($order_id);
    }

    public function getOptions(int $order_id, int $order_product_id): array {
        return $this->getMapper()->getOptionsArray($order_id, $order_product_id);
    }

    public function getVouchers(int $order_id): array {
        return $this->getMapper()->getVouchersArray($order_id);
    }

    public function getTotals(int $order_id): array {
        return $this->getMapper()->getTotalsArray($order_id);
    }

    public function getHistories(int $order_id): array {
        return $this->getMapper()->getHistoriesArray($order_id, $this->language_id);
    }

    public function getTotalHistories(int $order_id): int {
        return $this->getMapper()->getTotalHistoriesCount($order_id);
    }

    public function getSubscription(int $order_id, int $order_product_id): array {
        return $this->getMapper()->getSubscriptionArray($order_id, $order_product_id);
    }

    public function getOrdersBySubscriptionId(int $subscription_id, int $start = 0, int $limit = 20): array {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 20;
        }
        return $this->getMapper()->getOrdersBySubscriptionIdArray($subscription_id, $start, $limit);
    }

    public function getTotalOrdersBySubscriptionId(int $subscription_id): int {
        return $this->getMapper()->getTotalOrdersBySubscriptionIdCount($subscription_id);
    }

    // --- Implementações Obrigatórias da Interface BaseRepositoryInterface ---    

    public function find(int $id): ?InterfaceEntity
    {
        $mapper = $this->getMapper();
        return method_exists($mapper, 'findById') ? $mapper->findById($id) : null;
    }

    public function findAll(): array
    {
        $mapper = $this->getMapper();
        return method_exists($mapper, 'findAll') ? $mapper->findAll() : [];
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $mapper = $this->getMapper();
        return method_exists($mapper, 'search') ? $mapper->search($criteria, $orderBy, $limit, $offset) : [];
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $mapper = $this->getMapper();
        if (method_exists($mapper, 'search')) {
            $results = $mapper->search($criteria);
            return $results[0] ?? null;
        }
        return null;
    }

    /**
     * Retorna a listagem paginada e filtrada de pedidos para o Admin.
     */
    public function getAdminOrdersPaginated(array $filters, int $page = 1, int $limit = 15, ?int $languageId = null): array
    {
        $lId = $languageId ?? $this->language_id;
        return $this->getMapper()->getAdminOrdersPaginated($filters, $page, $limit, $lId);
    }
}
