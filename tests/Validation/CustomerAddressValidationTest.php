<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\DTOs\OrderDataDTO;

class CustomerAddressValidationTest extends TestCase
{
    private $container;
    private RepositoryFactory $repositoryFactory;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'catalog');
        }
        $bootstrap = AppBootstrap::boot();
        $this->container = $bootstrap->getContainer();
        $this->repositoryFactory = $this->container->get(RepositoryFactory::class);
    }

    public function testCustomerAddressCrud(): void
    {
        /** @var CustomerAddressesRepository $addressesRepo */
        $addressesRepo = $this->repositoryFactory->get(CustomerAddressesRepository::class);

        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();

        // Seed country 76 if missing
        $country76 = (int)$conn->query("SELECT id FROM `" . DB_PREFIX . "geo_countries` WHERE id = 76 LIMIT 1")->fetchColumn();
        if (!$country76) {
            $conn->prepare("INSERT INTO `" . DB_PREFIX . "geo_countries` (id, name, iso_code_2, iso_code_3, status) VALUES (76, 'Brasil', 'BR', 'BRA', 1)")->execute();
        }

        // Insert dedicated test customer
        $testEmail = 'testaddress_' . time() . '@example.com';
        $conn->prepare("INSERT INTO `" . DB_PREFIX . "customer` (store_id, customer_group_id, language_id, firstname, lastname, email, telephone, status, date_added) VALUES (1, 1, 2, 'Test', 'Customer', ?, '11999999999', 1, NOW())")->execute([$testEmail]);
        $testCustomerId = (int)$conn->lastInsertId();

        $countryId = (int)$conn->query("SELECT id FROM `" . DB_PREFIX . "geo_countries` LIMIT 1")->fetchColumn();
        if (!$countryId) {
            $conn->prepare("INSERT INTO `" . DB_PREFIX . "geo_countries` (id, name, iso_code_2, iso_code_3, status) VALUES (76, 'Brasil', 'BR', 'BRA', 1)")->execute();
            $countryId = 76;
        }

        $zoneId = (int)$conn->query("SELECT id FROM `" . DB_PREFIX . "geo_zones` LIMIT 1")->fetchColumn();
        if (!$zoneId) {
            $conn->prepare("INSERT INTO `" . DB_PREFIX . "geo_zones` (country_id, name, code, iso_code, status) VALUES (?, 'São Paulo', 'SP', 'BR-SP', 1)")->execute([$countryId]);
            $zoneId = (int)$conn->lastInsertId();
        }

        $addressData = [
            'postcode'     => '01001-000',
            'address_1'    => 'Praça da Sé',
            'street'       => 'Praça da Sé',
            'number'       => '111',
            'address_2'    => 'Apto 12',
            'complement'   => 'Apto 12',
            'neighborhood' => 'Sé',
            'district'     => 'Sé',
            'city'         => 'São Paulo',
            'zone_id'      => $zoneId,
            'country_id'   => $countryId,
            'default'      => false
        ];

        $savedId = $addressesRepo->save($addressData, $testCustomerId);
        $this->assertGreaterThan(0, $savedId);

        $addresses = $addressesRepo->getAddresses($testCustomerId);
        $this->assertNotEmpty($addresses);

        $savedAddress = $addresses[0];
        $this->assertEquals('01001-000', $savedAddress['postcode']);
        $this->assertEquals('Praça da Sé', $savedAddress['street']);
        $this->assertEquals('111', $savedAddress['number']);

        // Cleanup
        $addressesRepo->delete($savedId, $testCustomerId);
        $addressesAfterDelete = $addressesRepo->getAddresses($testCustomerId);
        $this->assertCount(0, $addressesAfterDelete);

        $conn->prepare("DELETE FROM `" . DB_PREFIX . "customer` WHERE id = ?")->execute([$testCustomerId]);
    }

    public function testOrderAddressesPersistenceAndMapping(): void
    {
        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->repositoryFactory->get(OrderRepository::class);
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $testCustomerId = (int)$conn->query("SELECT id FROM `" . DB_PREFIX . "customer` LIMIT 1")->fetchColumn() ?: 1;

        $orderData = [
            'store_id' => 1,
            'language_id' => 2,
            'currency_id' => 1,
            'customer_id' => $testCustomerId,
            'customer_group_id' => 1,
            'firstname' => 'João',
            'lastname' => 'Silva',
            'email' => 'joao@example.com',
            'telephone' => '11999999999',
            'payment_firstname' => 'João',
            'payment_lastname' => 'Silva',
            'payment_company' => 'Empresa Teste',
            'payment_street' => 'Rua de Teste',
            'payment_number' => 123,
            'payment_complement' => 'Sala A',
            'payment_district' => 'Centro',
            'payment_city' => 'São Paulo',
            'payment_postcode' => '01001-000',
            'payment_country' => 'Brasil',
            'payment_country_id' => 76,
            'payment_zone' => 'São Paulo',
            'payment_zone_id' => 31,
            'payment_method' => 'Pagar na Entrega',
            'payment_code' => 'cod',
            'shipping_firstname' => 'João',
            'shipping_lastname' => 'Silva',
            'shipping_company' => 'Empresa Teste',
            'shipping_street' => 'Rua de Entrega',
            'shipping_number' => 456,
            'shipping_complement' => 'Casa 1',
            'shipping_district' => 'Bairro Novo',
            'shipping_city' => 'Campinas',
            'shipping_postcode' => '13000-000',
            'shipping_country' => 'Brasil',
            'shipping_country_id' => 76,
            'shipping_zone' => 'São Paulo',
            'shipping_zone_id' => 31,
            'shipping_method' => 'Retirar na Loja',
            'shipping_code' => 'pickup',
            'products' => [
                [
                    'product_id' => 1,
                    'name' => 'Produto Teste',
                    'model' => 'PT1',
                    'quantity' => 1,
                    'price' => 10.0,
                    'total' => 10.0,
                    'tax' => 0.0,
                    'reward' => 0
                ]
            ],
            'totals' => [
                [
                    'code' => 'sub_total',
                    'title' => 'Sub-Total',
                    'value' => 10.0,
                    'sort_order' => 1
                ],
                [
                    'code' => 'total',
                    'title' => 'Total',
                    'value' => 10.0,
                    'sort_order' => 9
                ]
            ],
            'total' => 10.0,
            'comment' => 'Comentário de teste PHPUnit',
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test'
        ];

        $orderDto = new OrderDataDTO($orderData);
        $this->assertTrue($orderDto->isValid());

        $savedOrderId = $orderRepo->save($orderDto);
        $this->assertGreaterThan(0, $savedOrderId);

        $orderRepo->confirm($savedOrderId, 1, 'Confirming test order');

        $retrievedOrder = $orderRepo->getOrder($savedOrderId, $testCustomerId);
        $this->assertNotEmpty($retrievedOrder);

        $this->assertEquals('Rua de Teste', $retrievedOrder['payment_street']);
        $this->assertEquals(123, (int)$retrievedOrder['payment_number']);
        $this->assertEquals('Rua de Entrega', $retrievedOrder['shipping_street']);
        $this->assertEquals(456, (int)$retrievedOrder['shipping_number']);

        // Cleanup
        /** @var \Alpha\Mappers\EntityMappers\OrderMapper $orderMapper */
        $orderMapper = $this->container->get(\Alpha\Mappers\MapperFactory::class)->get(\Alpha\Mappers\EntityMappers\OrderMapper::class);
        $orderMapper->delete($savedOrderId);
    }
}
