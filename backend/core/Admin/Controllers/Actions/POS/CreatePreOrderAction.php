<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\POS;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Mappers\MapperFactory;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Model\Domain\DTOs\OrderDataDTO;
use Predis\Client as RedisClient;
use Exception;

/**
 * Action responsável por criar uma pré-venda (pedido pendente) e reservar o estoque correspondente.
 */
class CreatePreOrderAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = (string)$request->getBody();
        $data = json_decode($body, true) ?? [];

        $customerId = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $cart = isset($data['cart']) ? (array)$data['cart'] : [];
        $idempotencyKey = $request->getHeaderLine('Idempotency-Key') ?: ($data['idempotency_key'] ?? null);

        // 1. Tenta se conectar ao Redis para verificação de idempotência
        try {
            $redis = new RedisClient([
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                'timeout' => 1.0
            ]);
            $redis->connect();
        } catch (Exception $e) {
            $redis = null;
        }

        // 2. Verifica idempotência
        if ($idempotencyKey) {
            if ($redis) {
                // Bloqueia com TTL de 5 minutos
                $lock = $redis->set($idempotencyKey, 'PROCESSING', 'NX', 'EX', 300);
                if (!$lock) {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'error'   => 'DUPLICATE_REQUEST',
                        'message' => 'Pedido em andamento. Por favor, aguarde.'
                    ]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
                }
            } else {
                // Fallback para $_SESSION
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                if (isset($_SESSION['idempotency_keys'][$idempotencyKey])) {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'error'   => 'DUPLICATE_REQUEST',
                        'message' => 'Pedido em andamento. Por favor, aguarde.'
                    ]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
                }
                $_SESSION['idempotency_keys'][$idempotencyKey] = 'PROCESSING';
            }
        }

        try {
            if (empty($cart)) {
                throw new Exception('O carrinho de compras está vazio.');
            }

            /** @var CustomerRepository $customerRepo */
            $customerRepo = $this->getRepository(CustomerRepository::class);
            $customer = $customerId > 0 ? $customerRepo->find($customerId) : null;

            $firstname = $customer ? $customer->getFirstname() : 'Cliente';
            $lastname  = $customer ? $customer->getLastname() : 'PDV';
            $email     = $customer ? $customer->getEmail() : 'cliente.pdv@meusite.com';
            $telephone = $customer ? $customer->getTelephone() : '';

            /** @var ProductRepository $productRepo */
            $productRepo = $this->getRepository(ProductRepository::class);
            
            $productsData = [];
            $total = 0.0;

            foreach ($cart as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                $quantity = (int)($item['quantity'] ?? 1);

                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }

                $product = $productRepo->getProduct($productId);
                if (!$product) {
                    throw new Exception("Produto ID {$productId} não encontrado ou inativo no estoque.");
                }

                $stock = (int)($product['quantity'] ?? 0);
                if ($stock < $quantity) {
                    throw new Exception("Estoque insuficiente para o produto: {$product['name']}. Disponível: {$stock}.");
                }

                $price = (float)($product['special'] ?: $product['price']);
                $itemTotal = $price * $quantity;
                $total += $itemTotal;

                $productsData[] = [
                    'product_id' => $productId,
                    'name'       => $product['name'],
                    'model'      => $product['model'] ?? '',
                    'quantity'   => $quantity,
                    'price'      => $price,
                    'total'      => $itemTotal,
                    'tax'        => 0.0,
                    'reward'     => 0,
                    'option'     => []
                ];
            }

            if (empty($productsData)) {
                throw new Exception('Nenhum item válido adicionado ao carrinho.');
            }

            $totalsData = [
                [
                    'code'       => 'sub_total',
                    'title'      => 'Sub-Total',
                    'value'      => $total,
                    'sort_order' => 1,
                ],
                [
                    'code'       => 'total',
                    'title'      => 'Total',
                    'value'      => $total,
                    'sort_order' => 9,
                ],
            ];

            $orderData = [
                'store_id'              => $this->storeId,
                'customer_id'           => $customerId,
                'firstname'             => $firstname,
                'lastname'              => $lastname,
                'email'                 => $email,
                'telephone'             => $telephone,
                'payment_method'        => 'PDV - Pendente',
                'shipping_method'       => 'Retirada no Balcão',
                'total'                 => $total,
                'order_status_id'       => 1, // Status 1 = Pendente
                'store_name'            => 'meusite PDV',
                'store_url'             => HTTP_SERVER,
                'customer_group_id'     => $customer ? (int)$customer->getCustomerGroupId() : 1,
                'language_id'           => $this->languageId,
                'language_code'         => 'pt-br',
                'currency_id'           => 1,
                'currency_code'         => 'BRL',
                'currency_value'        => 1.0,
                'ip'                    => $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent'            => $request->getHeaderLine('User-Agent'),
                'products'              => $productsData,
                'totals'                => $totalsData,
                'payment_firstname'     => $firstname,
                'payment_lastname'      => $lastname,
                'payment_company'       => '',
                'payment_street'        => '',
                'payment_number'        => 0,
                'payment_complement'    => '',
                'payment_district'      => '',
                'payment_city'          => '',
                'payment_postcode'      => '',
                'payment_country'       => '',
                'payment_country_id'    => 0,
                'payment_zone'          => '',
                'payment_zone_id'       => 0,
                'payment_address_format'=> '',
                'shipping_firstname'    => $firstname,
                'shipping_lastname'     => $lastname,
                'shipping_company'      => '',
                'shipping_street'       => '',
                'shipping_number'       => 0,
                'shipping_complement'   => '',
                'shipping_district'     => '',
                'shipping_city'         => '',
                'shipping_postcode'     => '',
                'shipping_country'      => '',
                'shipping_country_id'   => 0,
                'shipping_zone'         => '',
                'shipping_zone_id'      => 0,
                'shipping_address_format'=> '',
                'comment'               => 'Pré-venda criada via PDV do Vendedor.',
                'tracking'              => '',
                'forwarded_ip'          => '',
                'accept_language'       => '',
                'payment_address_id'    => 0,
                'shipping_address_id'   => 0,
                'invoice_prefix'        => 'INV-',
            ];

            $orderDto = new OrderDataDTO($orderData);
            
            /** @var OrderRepository $orderRepo */
            $orderRepo = $this->getRepository(OrderRepository::class);
            $uow = new UnitOfWork();

            // 3. Executa a gravação e reserva de estoque dentro de uma transação atômica
            $orderId = $uow->transaction(function() use ($orderRepo, $productRepo, $orderDto, $productsData) {
                // Insere o pedido
                $id = $orderRepo->save($orderDto);

                // Deduz a quantidade do produto (reserva temporária)
                $productMapper = MapperFactory::getInstance()->get(ProductMapper::class);
                foreach ($productsData as $p) {
                    $product = $productRepo->getProduct($p['product_id']);
                    if ($product) {
                        $currentQty = (int)($product['quantity'] ?? 0);
                        $newQty = $currentQty - (int)$p['quantity'];
                        $productMapper->updateQuantity($p['product_id'], $newQty);
                    }
                }

                return $id;
            });

            // 4. Marca chave de idempotência como concluída com sucesso
            if ($idempotencyKey) {
                if ($redis) {
                    $redis->set($idempotencyKey, 'SUCCESS', 'EX', 300);
                } else {
                    $_SESSION['idempotency_keys'][$idempotencyKey] = 'SUCCESS';
                }
            }

            $response->getBody()->write(json_encode([
                'success'  => true,
                'order_id' => $orderId,
                'message'  => 'Pré-venda criada e estoque reservado com sucesso.'
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {
            // Libera a trava de idempotência em caso de falha
            if ($idempotencyKey) {
                if ($redis) {
                    $redis->del($idempotencyKey);
                } else {
                    unset($_SESSION['idempotency_keys'][$idempotencyKey]);
                }
            }

            $response->getBody()->write(json_encode([
                'success' => false,
                'error'   => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
