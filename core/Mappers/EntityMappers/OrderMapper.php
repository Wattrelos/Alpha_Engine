<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Order;
use Alpha\Model\Domain\Entities\OrderHistory;
use Alpha\Model\Domain\Entities\OrderProduct;
use Alpha\Model\Domain\Entities\OrderOption;
use Alpha\Model\Domain\Entities\OrderTotal;
use Alpha\Model\Domain\Observers\OrderObserverInterface;
use Alpha\Model\Domain\DTOs\OrderDataDTO;

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
    protected string $tableName = 'order';
    protected string $entityClass = Order::class;

    /** @var OrderObserverInterface[] */
    private array $observers = [];

    public function __construct($container = null)
    {
        parent::__construct($container);
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
    public function save(\Alpha\Model\Domain\InterfaceEntity $order): ?int
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

        // Alpha Engine: Também precisamos atualizar o order_status_id na tabela order correspondente!
        $sql = "UPDATE `" . DB_PREFIX . "order` SET order_status_id = ?, date_modified = NOW() WHERE id = ?";
        $this->dao->executeRawSQL($sql, [$orderStatusId, $orderId]);
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

    // --- Métodos de persistência e consulta legada migrados para PDO ---

    public function insert(OrderDataDTO $dto): int
    {
        $order = new Order();

        // Map DTO flat properties to the Order entity properties
        $order->setStoreId((int)$dto->get('store_id'))
            ->setCustomerId((int)$dto->get('customer_id'))
            ->setFirstname((string)$dto->get('firstname'))
            ->setLastname((string)$dto->get('lastname'))
            ->setEmail((string)$dto->get('email'))
            ->setTelephone((string)$dto->get('telephone'))
            ->setPaymentMethod((string)$dto->get('payment_method'))
            ->setShippingMethod((string)$dto->get('shipping_method'))
            ->setTotal((float)$dto->get('total'))
            ->setOrderStatusId((int)$dto->get('order_status_id', 0))
            ->setSubscriptionId((int)$dto->get('subscription_id', 0))
            ->setInvoiceNo((int)$dto->get('invoice_no', 0))
            ->setInvoicePrefix((string)$dto->get('invoice_prefix'))
            ->setTransactionId((string)$dto->get('transaction_id'))
            ->setStoreName((string)$dto->get('store_name'))
            ->setStoreUrl((string)$dto->get('store_url'))
            ->setCustomerGroupId((int)$dto->get('customer_group_id'))
            ->setPaymentAddressId((int)$dto->get('payment_address_id'))
            ->setPaymentFirstname((string)$dto->get('payment_firstname'))
            ->setPaymentLastname((string)$dto->get('payment_lastname'))
            ->setPaymentCompany((string)$dto->get('payment_company'))
            ->setPaymentStreet((string)$dto->get('payment_street'))
            ->setPaymentNumber((int)$dto->get('payment_number'))
            ->setPaymentComplement((string)$dto->get('payment_complement'))
            ->setPaymentDistrict((string)$dto->get('payment_district'))
            ->setPaymentCity((string)$dto->get('payment_city'))
            ->setPaymentPostcode((string)$dto->get('payment_postcode'))
            ->setPaymentCountry((string)$dto->get('payment_country'))
            ->setPaymentCountryId((int)$dto->get('payment_country_id'))
            ->setPaymentZone((string)$dto->get('payment_zone'))
            ->setPaymentZoneId((int)$dto->get('payment_zone_id'))
            ->setPaymentAddressFormat((string)$dto->get('payment_address_format'))
            ->setShippingAddressId((int)$dto->get('shipping_address_id'))
            ->setShippingFirstname((string)$dto->get('shipping_firstname'))
            ->setShippingLastname((string)$dto->get('shipping_lastname'))
            ->setShippingCompany((string)$dto->get('shipping_company'))
            ->setShippingStreet((string)$dto->get('shipping_street'))
            ->setShippingNumber((int)$dto->get('shipping_number'))
            ->setShippingComplement((string)$dto->get('shipping_complement'))
            ->setShippingDistrict((string)$dto->get('shipping_district'))
            ->setShippingCity((string)$dto->get('shipping_city'))
            ->setShippingPostcode((string)$dto->get('shipping_postcode'))
            ->setShippingCountry((string)$dto->get('shipping_country'))
            ->setShippingCountryId((int)$dto->get('shipping_country_id'))
            ->setShippingZone((string)$dto->get('shipping_zone'))
            ->setShippingZoneId((int)$dto->get('shipping_zone_id'))
            ->setShippingAddressFormat((string)$dto->get('shipping_address_format'))
            ->setComment((string)$dto->get('comment'))
            ->setAffiliateId((int)$dto->get('affiliate_id', 0))
            ->setCommission((float)$dto->get('commission', 0.0))
            ->setMarketingId((int)$dto->get('marketing_id', 0))
            ->setTracking((string)$dto->get('tracking'))
            ->setLanguageId((int)$dto->get('language_id'))
            ->setLanguageCode((string)$dto->get('language_code'))
            ->setCurrencyId((int)$dto->get('currency_id'))
            ->setCurrencyCode((string)$dto->get('currency_code', 'BRL'))
            ->setCurrencyValue((float)$dto->get('currency_value', 1.0))
            ->setIp((string)$dto->get('ip'))
            ->setForwardedIp((string)$dto->get('forwarded_ip'))
            ->setUserAgent((string)$dto->get('user_agent'))
            ->setAcceptLanguage((string)$dto->get('accept_language'))
            ->setDateAdded(date('Y-m-d H:i:s'))
            ->setDateModified(date('Y-m-d H:i:s'));

        // Map DTO products to OrderProduct entities
        $products = [];
        foreach ((array)$dto->get('products', []) as $productData) {
            $product = new OrderProduct();
            $product->setProductId((int)$productData['product_id'])
                ->setName((string)$productData['name'])
                ->setModel((string)$productData['model'])
                ->setQuantity((int)$productData['quantity'])
                ->setPrice((float)$productData['price'])
                ->setTotal((float)$productData['total'])
                ->setTax((float)$productData['tax'])
                ->setReward((int)$productData['reward'])
                ->setOrder($order);

            // Map product options to OrderOption entities
            $options = [];
            foreach ((array)($productData['option'] ?? []) as $optionData) {
                $option = new OrderOption();
                $option->setProductOptionId((int)$optionData['product_option_id'])
                    ->setProductOptionValueId((int)($optionData['product_option_value_id'] ?? 0))
                    ->setName((string)$optionData['name'])
                    ->setValue((string)$optionData['value'])
                    ->setType((string)$optionData['type'])
                    ->setOrder($order)
                    ->setOrderProduct($product);

                $options[] = $option;
            }
            $product->setOptions($options);
            $products[] = $product;
        }
        $order->setProducts($products);

        // Map DTO totals to OrderTotal entities
        $totals = [];
        foreach ((array)$dto->get('totals', []) as $totalData) {
            $total = new OrderTotal();
            $total->setCode((string)$totalData['code'])
                ->setTitle((string)$totalData['title'])
                ->setValue((float)$totalData['value'])
                ->setSortOrder((int)$totalData['sort_order'])
                ->setExtension((string)($totalData['extension'] ?? ''))
                ->setOrder($order);
            $totals[] = $total;
        }
        $order->setTotals($totals);

        $orderId = $this->save($order);
        return (int)$orderId;
    }

    public function getOrderArray(int $orderId, int $customerId = 0): array
    {
        $query = (new QueryBuilder())
            ->select('*', 'id AS order_id')
            ->from(DB_PREFIX . "order")
            ->where("id = ?", [$orderId])
            ->where("order_status_id > '0'", []);

        if ($customerId > 0) {
            $query->where("customer_id = ?", [$customerId]);
        }

        $results = $this->dao->executeQuery($query);
        return $results[0] ?? [];
    }

    public function getOrdersArray(int $customerId, int $storeId, int $languageId, int $start = 0, int $limit = 20): array
    {
        $query = (new QueryBuilder())
            ->select('o.id AS order_id', 'o.firstname', 'o.lastname', 'os.name as status', 'o.date_added', 'o.total', 'o.currency_code', 'o.currency_value', 'o.order_status_id')
            ->from(DB_PREFIX . "order", "o")
            ->leftJoin(DB_PREFIX . "order_status", "os", "o.order_status_id = os.id")
            ->where("o.customer_id = ?", [$customerId])
            ->where("o.order_status_id > '0'", [])
            ->where("o.store_id = ?", [$storeId])
            ->where("os.language_id = ?", [$languageId])
            ->orderBy("o.id", "DESC")
            ->limit($limit)
            ->offset($start);

        return $this->dao->executeQuery($query);
    }

    public function getTotalOrdersCount(int $customerId, int $storeId): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . "order")
            ->where("customer_id = ?", [$customerId])
            ->where("order_status_id > '0'", [])
            ->where("store_id = ?", [$storeId]);

        return $this->dao->executeCount($query);
    }

    public function getTotalProductsByOrderId(int $orderId): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . "order_product")
            ->where("order_id = ?", [$orderId]);

        return $this->dao->executeCount($query);
    }

    public function getProductsArray(int $orderId): array
    {
        $query = (new QueryBuilder())
            ->select('*', 'id AS order_product_id')
            ->from(DB_PREFIX . "order_product")
            ->where("order_id = ?", [$orderId]);

        return $this->dao->executeQuery($query);
    }

    public function getOptionsArray(int $orderId, int $orderProductId): array
    {
        $query = (new QueryBuilder())
            ->select('*', 'id AS order_option_id')
            ->from(DB_PREFIX . "order_option")
            ->where("order_id = ?", [$orderId])
            ->where("order_product_id = ?", [$orderProductId]);

        return $this->dao->executeQuery($query);
    }

    public function getVouchersArray(int $orderId): array
    {
        $query = (new QueryBuilder())
            ->select('*', 'id AS order_voucher_id')
            ->from(DB_PREFIX . "order_voucher")
            ->where("order_id = ?", [$orderId]);

        return $this->dao->executeQuery($query);
    }

    public function getTotalsArray(int $orderId): array
    {
        $query = (new QueryBuilder())
            ->select('*', 'id AS order_total_id')
            ->from(DB_PREFIX . "order_total")
            ->where("order_id = ?", [$orderId])
            ->orderBy("sort_order", "ASC");

        return $this->dao->executeQuery($query);
    }

    public function getHistoriesArray(int $orderId, int $languageId): array
    {
        $query = (new QueryBuilder())
            ->select('oh.date_added', 'os.name AS status', 'oh.comment', 'oh.notify')
            ->from(DB_PREFIX . "order_history", "oh")
            ->leftJoin(DB_PREFIX . "order_status", "os", "oh.order_status_id = os.id")
            ->where("oh.order_id = ?", [$orderId])
            ->where("os.language_id = ?", [$languageId])
            ->orderBy("oh.date_added", "ASC");

        return $this->dao->executeQuery($query);
    }

    public function getTotalHistoriesCount(int $orderId): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . "order_history")
            ->where("order_id = ?", [$orderId]);

        return $this->dao->executeCount($query);
    }

    public function getSubscriptionArray(int $orderId, int $orderProductId): array
    {
        $query = (new QueryBuilder())
            ->select('*', 'id AS order_subscription_id')
            ->from(DB_PREFIX . "order_subscription")
            ->where("order_id = ?", [$orderId])
            ->where("order_product_id = ?", [$orderProductId])
            ->limit(1);

        $results = $this->dao->executeQuery($query);
        return $results[0] ?? [];
    }

    public function getOrdersBySubscriptionIdArray(int $subscriptionId, int $start = 0, int $limit = 20): array
    {
        $query = (new QueryBuilder())
            ->select('o.id AS order_id', 'o.total', 'o.currency_code', 'o.currency_value', 'o.date_added')
            ->from(DB_PREFIX . "order", "o")
            ->where("o.subscription_id = ?", [$subscriptionId])
            ->orderBy("o.id", "DESC")
            ->limit($limit)
            ->offset($start);

        return $this->dao->executeQuery($query);
    }

    public function getTotalOrdersBySubscriptionIdCount(int $subscriptionId): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . "order")
            ->where("subscription_id = ?", [$subscriptionId]);

        return $this->dao->executeCount($query);
    }
}
