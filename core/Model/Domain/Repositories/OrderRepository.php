<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\DTOs\OrderDataDTO;
use Alpha\Mappers\OrderMapper;
use Alpha\Mappers\CartMapper;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Factories\MapperFactory;
use Opencart\System\Engine\Registry;

/**
 * Class OrderRepository
 * 
 * Gerencia a lógica de negócio e orquestração de persistência de pedidos.
 */
class OrderRepository extends AbstractRepository implements BaseRepositoryInterface {

    private UnitOfWork $unitOfWork;
    private Registry $registry;
    private MapperFactory $mapperFactory;

    /**
     * Construtor com injeção de dependências rigorosa.
     */
    public function __construct(
        OrderMapper $mapper,
        UnitOfWork $unitOfWork,
        Registry $registry,
        MapperFactory $mapperFactory
    ) {
        parent::__construct($mapper);
        $this->unitOfWork = $unitOfWork;
        $this->registry = $registry;
        $this->mapperFactory = $mapperFactory;
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
     * Cria um pedido a partir do estado atual da sessão do usuário.
     * 
     * @return int ID do pedido criado.
     * @throws \Exception
     */
    public function createFromSession(): int {
        // 1. Coleta e sanitização dos dados brutos do contexto da aplicação
        $orderData = $this->collectDataFromContext();

        // 2. Encapsulamento em um DTO (Isolamento da camada de dados)
        $dto = new OrderDataDTO($orderData);

        if (!$dto->isValid()) {
            throw new \Exception('Alpha Engine: Tentativa de criar pedido com dados insuficientes na sessão.');
        }

        // 3. Persistência delegada
        return $this->save($dto);
    }

    /**
     * Persiste o DTO de pedido no banco de dados através do Mapper.
     */
    public function save(OrderDataDTO $dto): int {
        /** @var OrderMapper $mapper */
        $mapper = $this->getMapper();
        
        return $mapper->insert($dto);
    }

    /**
     * Coleta informações do cliente, carrinho, endereços e totais do Registry.
     */
    private function collectDataFromContext(): array {
        $session = $this->registry->get('session');
        $customer = $this->registry->get('customer');
        $config = $this->registry->get('config');
        $cart = $this->registry->get('cart');

        $data = [];

        // Configurações Contextuais
        $data['store_id'] = (int)$config->get('config_store_id');
        $data['language_id'] = (int)$config->get('config_language_id');
        $data['currency_id'] = (int)$config->get('config_currency_id');

        // Dados do Cliente (Fallback para sessão se não logado)
        $data['customer_id'] = (int)$customer->getId();
        $data['customer_group_id'] = (int)$customer->getGroupId();
        $data['firstname'] = $customer->getFirstName() ?: ($session->data['payment_address']['firstname'] ?? '');
        $data['lastname'] = $customer->getLastName() ?: ($session->data['payment_address']['lastname'] ?? '');
        $data['email'] = $customer->getEmail() ?: ($session->data['email'] ?? '');
        $data['telephone'] = $customer->getTelephone() ?: ($session->data['telephone'] ?? '');

        // Endereços e Métodos (Extraídos da sessão de checkout)
        $payment_address = $session->data['payment_address'] ?? [];
        $shipping_address = $session->data['shipping_address'] ?? [];

        $data['payment_firstname'] = $payment_address['firstname'] ?? '';
        $data['payment_lastname'] = $payment_address['lastname'] ?? '';
        $data['payment_address_1'] = $payment_address['address_1'] ?? '';
        $data['payment_city'] = $payment_address['city'] ?? '';
        $data['payment_postcode'] = $payment_address['postcode'] ?? '';
        $data['payment_country_id'] = (int)($payment_address['country_id'] ?? 0);
        $data['payment_zone_id'] = (int)($payment_address['zone_id'] ?? 0);
        $data['payment_method'] = $session->data['payment_method']['title'] ?? '';
        $data['payment_code'] = $session->data['payment_method']['code'] ?? '';

        $data['shipping_firstname'] = $shipping_address['firstname'] ?? '';
        $data['shipping_lastname'] = $shipping_address['lastname'] ?? '';
        $data['shipping_address_1'] = $shipping_address['address_1'] ?? '';
        $data['shipping_city'] = $shipping_address['city'] ?? '';
        $data['shipping_postcode'] = $shipping_address['postcode'] ?? '';
        $data['shipping_country_id'] = (int)($shipping_address['country_id'] ?? 0);
        $data['shipping_zone_id'] = (int)($shipping_address['zone_id'] ?? 0);
        $data['shipping_method'] = $session->data['shipping_method']['title'] ?? '';
        $data['shipping_code'] = $session->data['shipping_method']['code'] ?? '';

        // Itens do Carrinho via Mapper para garantir tipos Alpha Engine
        /** @var CartMapper $cartMapper */
        $cartMapper = $this->mapperFactory->get(CartMapper::class);
        
        $data['products'] = $cartMapper->getProducts($data['customer_id'], $session->getId(), $data['language_id'], $data['store_id'], $data['customer_group_id']);
        $data['vouchers'] = $session->data['vouchers'] ?? [];
        $data['totals'] = $session->data['totals'] ?? [];
        $data['total'] = $cart->getTotal();

        // Alpha Engine: Coleta de Cupom para rastreamento de marketing e histórico de uso
        $coupon_code = $session->data['coupon'] ?? '';
        $data['coupon_id'] = 0;
        $data['coupon_amount'] = 0.0;

        if ($coupon_code) {
            $db = $this->registry->get('db');
            $query = $db->query("SELECT id FROM " . DB_PREFIX . "coupon WHERE code = '" . $db->escape($coupon_code) . "'");
            
            if ($query->num_rows) {
                $data['coupon_id'] = (int)$query->row['id'];
                
                foreach ($data['totals'] as $total) {
                    if ($total['code'] === 'coupon') {
                        $data['coupon_amount'] = abs((float)$total['value']);
                        break;
                    }
                }
            }
        }
        
        // Metadados de Auditoria
        $data['ip'] = $this->registry->get('request')->server['REMOTE_ADDR'];
        $data['user_agent'] = $this->registry->get('request')->server['HTTP_USER_AGENT'] ?? '';

        return $data;
    }

    // --- Legacy Bridges (account/order) ---

    public function getOrder(int $order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0'");
        return $query->row;
    }

    public function getOrders(int $start = 0, int $limit = 20): array {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 20;
        }
        $query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value, o.order_status_id FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);
        return $query->rows;
    }

    public function getTotalOrders(): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");
        return (int)$query->row['total'];
    }

    public function getTotalProductsByOrderId(int $order_id): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
        return (int)$query->row['total'];
    }

    public function getProducts(int $order_id): array {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
        return $query->rows;
    }

    public function getOptions(int $order_id, int $order_product_id): array {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");
        return $query->rows;
    }

    public function getVouchers(int $order_id): array {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");
        return $query->rows;
    }

    public function getTotals(int $order_id): array {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");
        return $query->rows;
    }

    public function getHistories(int $order_id): array {
        $query = $this->db->query("SELECT date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC");
        return $query->rows;
    }

    public function getTotalHistories(int $order_id): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");
        return (int)$query->row['total'];
    }

    public function getSubscription(int $order_id, int $order_product_id): array {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_subscription WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");
        return $query->row;
    }

    public function getOrdersBySubscriptionId(int $subscription_id, int $start = 0, int $limit = 20): array {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 20;
        }
        $query = $this->db->query("SELECT o.order_id, o.total, o.currency_code, o.currency_value, o.date_added FROM `" . DB_PREFIX . "order` o WHERE o.subscription_id = '" . (int)$subscription_id . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);
        return $query->rows;
    }

    public function getTotalOrdersBySubscriptionId(int $subscription_id): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE subscription_id = '" . (int)$subscription_id . "'");
        return (int)$query->row['total'];
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
}