<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Mappers\EntityMappers\OrderMapper;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Mappers\MapperFactory;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Model\Domain\DTOs\OrderDataDTO;

class PosCashierValidationTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'admin');
        }
        AppBootstrap::boot();
    }

    public function testPosCashierActionsExist(): void
    {
        $actions = [
            \Alpha\Admin\Controllers\Actions\POS\ShowCashierDashboardAction::class,
            \Alpha\Admin\Controllers\Actions\POS\GetPreOrderAction::class,
            \Alpha\Admin\Controllers\Actions\POS\PayOrderAction::class,
        ];

        foreach ($actions as $action) {
            $this->assertTrue(class_exists($action), "A classe {$action} deve existir.");
        }
    }

    public function testPosPreOrderLifecycleAndStockDeduction(): void
    {
        $conn = ConnectionDB::getInstance()->getConnection();
        $testProduct = $conn->query("SELECT p.id, p.model, p.price, p.quantity, pd.name FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.id = pd.product_id WHERE p.status = 1 LIMIT 1")->fetch(\PDO::FETCH_ASSOC);

        if (!$testProduct) {
            $conn->prepare("INSERT INTO `" . DB_PREFIX . "product` (master_id, model, quantity, stock_status_id, tax_class_id, weight_class_id, length_class_id, price, status, date_added, ncm, cest) VALUES (0, 'TEST-POS', 10, 7, 0, 0, 0, 50.00, 1, NOW(), '', '')")->execute();
            $productId = (int)$conn->lastInsertId();
            $conn->prepare("INSERT INTO `" . DB_PREFIX . "product_description` (product_id, language_id, name) VALUES (?, 2, 'Produto Teste POS')")->execute([$productId]);
            $testProduct = ['id' => $productId, 'product_id' => $productId, 'model' => 'TEST-POS', 'price' => 50.00, 'special' => 0, 'quantity' => 10, 'name' => 'Produto Teste POS'];
        }

        $productId = (int)($testProduct['product_id'] ?? $testProduct['id']);
        $initialQty = (int)($testProduct['quantity'] ?? 0);

        /** @var CustomerRepository $customerRepo */
        $customerRepo = RepositoryFactory::getInstance()->get(CustomerRepository::class);
        $customers = $customerRepo->findAll();
        $testCustomer = !empty($customers) ? reset($customers) : null;

        $customerId = $testCustomer ? $testCustomer->getId() : 0;
        $firstname = $testCustomer ? $testCustomer->getFirstname() : 'Cliente';
        $lastname = $testCustomer ? $testCustomer->getLastname() : 'PDV';
        $email = $testCustomer ? $testCustomer->getEmail() : 'cliente.pdv@agsonhos.com';

        $price = (float)($testProduct['special'] ?: $testProduct['price']);
        $total = $price * 1;

        $orderData = [
            'store_id'              => 1,
            'customer_id'           => $customerId,
            'firstname'             => $firstname,
            'lastname'              => $lastname,
            'email'                 => $email,
            'telephone'             => $testCustomer ? $testCustomer->getTelephone() : '',
            'payment_method'        => 'PDV - Pendente',
            'shipping_method'       => 'Retirada no Balcão',
            'total'                 => $total,
            'order_status_id'       => 1,
            'store_name'            => 'AgSonhos PDV Teste Caixa',
            'store_url'             => 'http://localhost/',
            'customer_group_id'     => 1,
            'language_id'           => 2,
            'language_code'         => 'pt-br',
            'currency_id'           => 1,
            'currency_code'         => 'BRL',
            'currency_value'        => 1.0,
            'ip'                    => '127.0.0.1',
            'user_agent'            => 'TestAgent',
            'products'              => [
                [
                    'product_id' => $productId,
                    'name'       => $testProduct['name'],
                    'model'      => $testProduct['model'] ?? '',
                    'quantity'   => 1,
                    'price'      => $price,
                    'total'      => $total,
                    'tax'        => 0.0,
                    'reward'     => 0,
                ]
            ],
            'totals'                => [
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
            ],
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
            'comment'               => 'Pedido gerado pelo teste PHPUnit.',
            'tracking'              => '',
            'forwarded_ip'          => '',
            'accept_language'       => '',
            'payment_address_id'    => 0,
            'shipping_address_id'   => 0,
            'invoice_prefix'        => 'INV-',
        ];

        $orderDto = new OrderDataDTO($orderData);

        /** @var OrderRepository $orderRepo */
        $orderRepo = RepositoryFactory::getInstance()->get(OrderRepository::class);
        $uow = new UnitOfWork();

        $orderId = $uow->transaction(function() use ($orderRepo, $orderDto, $productId, $initialQty) {
            $id = $orderRepo->save($orderDto);
            $productMapper = MapperFactory::getInstance()->get(ProductMapper::class);
            $productMapper->updateQuantity($productId, $initialQty - 1);
            return $id;
        });

        $this->assertGreaterThan(0, $orderId);

        // Fetch order
        $fetchedOrder = $orderRepo->getOrder($orderId);
        $this->assertNotEmpty($fetchedOrder);
        $this->assertEquals(1, (int)$fetchedOrder['order_status_id']);

        // Pay order & update status to 5
        $uow->transaction(function() use ($orderRepo, $orderId) {
            $orderRepo->confirm($orderId, 5, 'Pagamento processado via PHPUnit');
        });

        $fetchedOrderAfter = $orderRepo->getOrder($orderId);
        $this->assertEquals(5, (int)$fetchedOrderAfter['order_status_id']);

        // Check stock quantity was not double deducted
        $stmt = ConnectionDB::getInstance()->getConnection()->prepare("SELECT quantity FROM `" . DB_PREFIX . "product` WHERE id = ?");
        $stmt->execute([$productId]);
        $qtyAfterPay = (int)$stmt->fetchColumn();
        $this->assertEquals($initialQty - 1, $qtyAfterPay, "Estoque deve ser mantido reservado sem dupla dedução.");

        // Cleanup
        $uow->transaction(function() use ($orderId, $productId, $initialQty) {
            /** @var OrderMapper $orderMapper */
            $orderMapper = MapperFactory::getInstance()->get(OrderMapper::class);
            $orderMapper->delete($orderId);

            /** @var ProductMapper $productMapper */
            $productMapper = MapperFactory::getInstance()->get(ProductMapper::class);
            $productMapper->updateQuantity($productId, $initialQty);
        });

        $stmt->execute([$productId]);
        $qtyRestored = (int)$stmt->fetchColumn();
        $this->assertEquals($initialQty, $qtyRestored, "Estoque deve ser restaurado ao valor inicial.");
    }
}
