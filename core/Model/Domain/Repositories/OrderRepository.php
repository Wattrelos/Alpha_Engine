<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\DTOs\OrderDataDTO;
use Alpha\Mappers\OrderMapper;
use Alpha\Mappers\CartMapper;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Support\Factories\MapperFactory;
use Opencart\System\Engine\Registry;

/**
 * Class OrderRepository
 * 
 * Gerencia a lógica de negócio e orquestração de persistência de pedidos.
 */
class OrderRepository extends AbstractRepository {

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
}