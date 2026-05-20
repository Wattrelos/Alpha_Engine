<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;

use Alpha\Model\Domain\DTOs\OrderDataDTO;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Model\Domain\Entities\Order;
use Alpha\Model\Domain\Entities\OrderHistory;
use Alpha\Model\Domain\Entities\OrderProduct;
use Alpha\Model\Domain\Entities\OrderTotal;
use Alpha\Model\Domain\Entities\OrderVoucher;
use Opencart\System\Engine\Registry;
use Alpha\Model\Domain\InterfaceEntity;
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
class OrderMapper extends BaseMapper
{
    protected string $entityClass = Order::class;
    protected string $tableName = 'order';

    /** @var OrderObserverInterface[] */
    private array $observers = [];

    /** @var UnitOfWork */
    private UnitOfWork $unitOfWork;

    public function __construct(Registry $registry = null)
    {
        parent::__construct($registry);

        // Alpha Engine: Resolução do serviço UnitOfWork para orquestração de transações atômicas
        if (!$this->registry->has('unit_of_work')) {
            $this->registry->set('unit_of_work', new UnitOfWork());
        }
        $this->unitOfWork = $this->registry->get('unit_of_work');
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
    public function save(InterfaceEntity $entity): ?int
    {
        $orderId = ($entity->getId() > 0) ? $this->dao->update($entity) : $this->dao->create($entity);

        if ($orderId && $entity instanceof Order) {
            $entity->setId($orderId);
            $this->notify($entity);
        }

        return $orderId;
    }

    /**
     * Alpha Engine: Insere um novo pedido utilizando o contrato OrderDataDTO.
     * Implementa tipagem estrita no QueryBuilder para garantir integridade física no MySQL.
     */
    public function insert(OrderDataDTO $dto): int {
        return $this->unitOfWork->transaction(function() use ($dto) {
            $builder = (new QueryBuilder())
                ->insert(DB_PREFIX . 'order')
                ->set('store_id', (int)$dto->get('store_id', 0))
                ->set('language_id', (int)$dto->get('language_id', 0))
                ->set('customer_id', (int)$dto->get('customer_id', 0))
                ->set('customer_group_id', (int)$dto->get('customer_group_id', 0))
                ->set('firstname', (string)$dto->get('firstname', ''))
                ->set('lastname', (string)$dto->get('lastname', ''))
                ->set('email', (string)$dto->get('email', ''))
                ->set('telephone', (string)$dto->get('telephone', ''))
                ->set('payment_firstname', (string)$dto->get('payment_firstname', ''))
                ->set('payment_lastname', (string)$dto->get('payment_lastname', ''))
                ->set('payment_address_1', (string)$dto->get('payment_address_1', ''))
                ->set('payment_city', (string)$dto->get('payment_city', ''))
                ->set('payment_postcode', (string)$dto->get('payment_postcode', ''))
                ->set('payment_country_id', (int)$dto->get('payment_country_id', 0))
                ->set('payment_zone_id', (int)$dto->get('payment_zone_id', 0))
                ->set('payment_method', (string)$dto->get('payment_method', ''))
                ->set('payment_code', (string)$dto->get('payment_code', ''))
                ->set('shipping_firstname', (string)$dto->get('shipping_firstname', ''))
                ->set('shipping_lastname', (string)$dto->get('shipping_lastname', ''))
                ->set('shipping_address_1', (string)$dto->get('shipping_address_1', ''))
                ->set('shipping_city', (string)$dto->get('shipping_city', ''))
                ->set('shipping_postcode', (string)$dto->get('shipping_postcode', ''))
                ->set('shipping_country_id', (int)$dto->get('shipping_country_id', 0))
                ->set('shipping_zone_id', (int)$dto->get('shipping_zone_id', 0))
                ->set('shipping_method', (string)$dto->get('shipping_method', ''))
                ->set('shipping_code', (string)$dto->get('shipping_code', ''))
                ->set('total', (float)$dto->get('total', 0.0))
                ->set('currency_id', (int)$dto->get('currency_id', 0))
                ->set('ip', (string)$dto->get('ip', ''))
                ->set('user_agent', (string)$dto->get('user_agent', ''))
                ->set('date_added', date('Y-m-d H:i:s'))
                ->set('date_modified', date('Y-m-d H:i:s'));

            $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
            if (!$conn instanceof \PDO) {
                throw new \RuntimeException("Erro Alpha Engine: Conexão PDO não disponível para inserção de pedido.");
            }
            $stmt = $conn->prepare($builder->getSQL());
            $stmt->execute($builder->getParams());

            $orderId = (int)$conn->lastInsertId();

            // Alpha Engine: Persistência atômica dos produtos do pedido
            foreach ((array)$dto->get('products', []) as $product) {
                $productBuilder = (new QueryBuilder())
                    ->insert(DB_PREFIX . 'order_product')
                    ->set('order_id', $orderId)
                    ->set('product_id', (int)$product['product_id'])
                    ->set('name', (string)$product['name'])
                    ->set('model', (string)$product['model'])
                    ->set('quantity', (int)$product['quantity'])
                    ->set('price', (float)$product['price'])
                    ->set('total', (float)$product['total'])
                    ->set('tax', (float)($product['tax'] ?? 0))
                    ->set('reward', (int)($product['reward'] ?? 0));
                
                $conn->prepare($productBuilder->getSQL())->execute($productBuilder->getParams());

                $orderProductId = (int)$conn->lastInsertId();

                // Alpha Engine: Persistência atômica das opções dos produtos (Variações)
                foreach ((array)($product['option'] ?? []) as $option) {
                    $optionBuilder = (new QueryBuilder())
                        ->insert(DB_PREFIX . 'order_option')
                        ->set('order_id', $orderId)
                        ->set('order_product_id', $orderProductId)
                        ->set('product_option_id', (int)$option['product_option_id'])
                        ->set('product_option_value_id', (int)$option['product_option_value_id'])
                        ->set('name', (string)$option['name'])
                        ->set('value', (string)$option['value'])
                        ->set('type', (string)$option['type']);

                    $conn->prepare($optionBuilder->getSQL())->execute($optionBuilder->getParams());
                }

                // Alpha Engine: Persistência atômica da assinatura vinculada ao produto (Snapshot Pattern)
                if (!empty($product['subscription'])) {
                    $subscription = $product['subscription'];
                    
                    $subscriptionBuilder = (new QueryBuilder())
                        ->insert(DB_PREFIX . 'order_subscription')
                        ->set('order_id', $orderId)
                        ->set('order_product_id', $orderProductId)
                        ->set('subscription_plan_id', (int)$subscription['subscription_plan_id'])
                        ->set('name', (string)$subscription['name'])
                        ->set('description', (string)$subscription['description'])
                        ->set('trial_price', (float)$subscription['trial_price'])
                        ->set('trial_frequency', (string)$subscription['trial_frequency'])
                        ->set('trial_cycle', (int)$subscription['trial_cycle'])
                        ->set('trial_duration', (int)$subscription['trial_duration'])
                        ->set('trial_status', (int)$subscription['trial_status'])
                        ->set('price', (float)$subscription['price'])
                        ->set('frequency', (string)$subscription['frequency'])
                        ->set('cycle', (int)$subscription['cycle'])
                        ->set('duration', (int)$subscription['duration'])
                        ->set('status', (int)($subscription['status'] ?? 0))
                        ->set('date_added', date('Y-m-d H:i:s'));

                    $conn->prepare($subscriptionBuilder->getSQL())->execute($subscriptionBuilder->getParams());
                }
            }

            // Alpha Engine: Persistência atômica das linhas de totalização (frete, taxas, descontos)
            foreach ((array)$dto->get('totals', []) as $total) {
                $totalBuilder = (new QueryBuilder())
                    ->insert(DB_PREFIX . 'order_total')
                    ->set('order_id', $orderId)
                    ->set('extension', (string)($total['extension'] ?? ''))
                    ->set('code', (string)$total['code'])
                    ->set('title', (string)$total['title'])
                    ->set('value', (float)$total['value'])
                    ->set('sort_order', (int)$total['sort_order']);
                
                $conn->prepare($totalBuilder->getSQL())->execute($totalBuilder->getParams());
            }

            // Alpha Engine: Persistência atômica dos vouchers de presente comprados no pedido
            foreach ((array)$dto->get('vouchers', []) as $voucher) {
                $voucherBuilder = (new QueryBuilder())
                    ->insert(DB_PREFIX . 'order_voucher')
                    ->set('order_id', $orderId)
                    ->set('description', (string)$voucher['description'])
                    ->set('code', (string)$voucher['code'])
                    ->set('from_name', (string)$voucher['from_name'])
                    ->set('from_email', (string)$voucher['from_email'])
                    ->set('to_name', (string)$voucher['to_name'])
                    ->set('to_email', (string)$voucher['to_email'])
                    ->set('voucher_theme_id', (int)$voucher['voucher_theme_id'])
                    ->set('message', (string)$voucher['message'])
                    ->set('amount', (float)$voucher['amount']);

                $conn->prepare($voucherBuilder->getSQL())->execute($voucherBuilder->getParams());
            }

            // Alpha Engine: Persistência do uso do cupom (Snapshot Pattern)
            // O registro histórico é essencial para congelar os benefícios aplicados ao pedido.
            if ($dto->get('coupon_id')) {
                $this->persistCouponSnapshot($conn, $orderId, $dto);
            }

            return $orderId;
        });
    }

    /**
     * Alpha Engine: Grava o snapshot do uso do cupom no histórico.
     * Registra o valor exato do desconto no momento da venda para integridade histórica.
     */
    private function persistCouponSnapshot(\PDO $conn, int $orderId, OrderDataDTO $dto): void
    {
        $builder = (new QueryBuilder())
            ->insert(DB_PREFIX . 'coupon_history')
            ->set('coupon_id', (int)$dto->get('coupon_id'))
            ->set('order_id', $orderId)
            ->set('customer_id', (int)$dto->get('customer_id', 0))
            ->set('amount', (float)$dto->get('coupon_amount', 0.0))
            ->set('date_added', date('Y-m-d H:i:s'));

        $conn->prepare($builder->getSQL())->execute($builder->getParams());
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
        // Alpha Engine: A baixa de estoque agora é parte integrante do histórico, 
        // permitindo que o UnitOfWork do Controller gerencie a transação.
        $history = new OrderHistory();
        $history->setOrderId($orderId)
            ->setOrderStatusId($orderStatusId)
            ->setComment($comment)
            ->setNotify($notify)
            ->setDateAdded(date('Y-m-d H:i:s'));

        $this->dao->create($history);

        // Verifica se este status de pedido exige a subtração de estoque
        $processing_status = (array)oc_config('config_processing_status');
        $complete_status = (array)oc_config('config_complete_status');

        if (in_array($orderStatusId, array_merge($processing_status, $complete_status))) {
            $this->subtractStock($orderId);
        }
    }

    /**
     * Alpha Engine: Realiza a baixa definitiva do estoque de produtos e opções.
     * Chamado internamente por addHistory durante transações atômicas.
     */
    public function subtractStock(int $orderId): void {
        $productMapper = new ProductMapper();

        // 1. Obtém os produtos do pedido diretamente via SQL (QueryBuilder)
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'order_product')
            ->where("order_id = ?", [$orderId])
            ->select('*');
        
        $order_products = $this->dao->executeQuery($query);

        foreach ($order_products as $order_product) {
            $product_id = (int)$order_product['product_id'];
            $quantity = (int)$order_product['quantity'];

            // Baixa estoque do produto principal (se configurado para subtrair)
            $p_query = (new QueryBuilder())
                ->from(DB_PREFIX . 'product')
                ->where("id = ?", [$product_id])
                ->where("subtract = '1'")
                ->select('quantity');
            
            $p_data = $this->dao->executeQuery($p_query);
            if ($p_data) {
                $productMapper->updateQuantity($product_id, $p_data[0]['quantity'] - $quantity);
            }

            // Baixa estoque das variações (Opções)
            $o_query = (new QueryBuilder())
                ->from(DB_PREFIX . 'order_option')
                ->where("order_product_id = ?", [(int)$order_product['order_product_id']])
                ->select('product_option_id', 'product_option_value_id');
            
            $order_options = $this->dao->executeQuery($o_query);
            foreach ($order_options as $order_option) {
                $pov_id = (int)$order_option['product_option_value_id'];
                
                $pov_query = (new QueryBuilder())
                    ->from(DB_PREFIX . 'product_option_value')
                    ->where("id = ?", [$pov_id])
                    ->where("subtract = '1'")
                    ->select('quantity');
                
                $pov_data = $this->dao->executeQuery($pov_query);
                if ($pov_data) {
                    $productMapper->updateOptionQuantity($product_id, (int)$order_option['product_option_id'], $pov_id, $pov_data[0]['quantity'] - $quantity);
                }
            }
        }
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
        
        $orders = [];
        foreach ($pagination['data'] as $row) {
            $order = $this->getOrder((int)$row['id']);
            if ($order) {
                $orders[] = $order;
            }
        }

        return $orders;
    }
}