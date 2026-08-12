<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Twig\Environment;
use Slim\Psr7\Factory\ServerRequestFactory;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Mappers\EntityMappers\ProductMapper;

class ProductValidationTest extends TestCase
{
    private $app;
    private $conn;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'admin');
        }

        $bootstrap = AppBootstrap::boot();
        $container = $bootstrap->getContainer();
        $this->conn = ConnectionDB::getInstance()->getConnection();

        $twig = Twig::create(__DIR__ . '/../../resources/views', [
            'cache'       => false,
            'auto_reload' => true,
            'debug'       => true,
        ]);
        $container->bind(Environment::class, $twig->getEnvironment());
        $container->bind(Twig::class, $twig);

        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $app->map(['GET', 'POST'], '/produtos/criar', \Alpha\Admin\Controllers\Actions\Catalog\Product\CreateProductAction::class);

        $this->app = $app;
    }

    public function testCreateProductFormRender(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest('GET', '/produtos/criar');
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        $this->assertStringContainsString('name="name"', $body);
        $this->assertStringContainsString('name="model"', $body);
    }

    public function testCreateProductValidationFailure(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest('POST', '/produtos/criar')
            ->withParsedBody(['model' => 'TEST-MODEL-INVALID']);

        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateProductPersistenceAndCleanup(): void
    {
        $testProductName = 'Test Product Action ' . time();
        $testProductModel = 'TEST-MODEL-' . time();
        $testProductPrice = 99.99;
        $testProductQuantity = 100;

        $testCategoryId = (int)$this->conn->query("SELECT id FROM `" . DB_PREFIX . "category` LIMIT 1")->fetchColumn();
        $createdTestCategory = false;
        if (!$testCategoryId) {
            $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "category` (`parent_id`, `sort_order`, `status`, `date_added`, `date_modified`) VALUES (0, 0, 1, NOW(), NOW())")->execute();
            $testCategoryId = (int)$this->conn->lastInsertId();
            $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, 1, 'Test Cat', '', '', '', '')")->execute([$testCategoryId]);
            $createdTestCategory = true;
        }

        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest('POST', '/produtos/criar')
            ->withParsedBody([
                'name' => $testProductName,
                'model' => $testProductModel,
                'price' => $testProductPrice,
                'quantity' => $testProductQuantity,
                'status' => 1,
                'description' => 'Detailed test description here.',
                'ean' => '1234567890123',
                'stock_status_id' => 7,
                'manufacturer_id' => 0,
                'date_available' => date('Y-m-d'),
                'product_category' => [$testCategoryId]
            ]);

        $response = $this->app->handle($request);
        $this->assertEquals(302, $response->getStatusCode());

        $stmt = $this->conn->prepare("
            SELECT p.id, pd.name, p.model
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.id = pd.product_id
            WHERE p.model = ?
        ");
        $stmt->execute([$testProductModel]);
        $insertedProduct = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($insertedProduct, "Produto inserido deve ser encontrado no banco.");
        $this->assertEquals($testProductName, $insertedProduct['name']);

        // Cleanup
        $productId = (int)$insertedProduct['id'];
        $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = ?")->execute([$productId]);
        $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE product_id = ?")->execute([$productId]);
        $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "product_description` WHERE product_id = ?")->execute([$productId]);
        $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "product` WHERE id = ?")->execute([$productId]);

        if ($createdTestCategory) {
            $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "category_description` WHERE category_id = ?")->execute([$testCategoryId]);
            $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "category` WHERE id = ?")->execute([$testCategoryId]);
        }
    }

    public function testStockStatusHidingRules(): void
    {
        $mapper = new ProductMapper();
        $modelA = 'TEST-HIDE-A';
        $modelB = 'TEST-HIDE-B';
        $modelC = 'TEST-HIDE-C';

        $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "product` WHERE model IN (?, ?, ?)")->execute([$modelA, $modelB, $modelC]);

        // Product A: quantity = 10, stock_status_id = 5
        $stmt = $this->conn->prepare("
            INSERT INTO `" . DB_PREFIX . "product` (
                `master_id`, `model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, 
                `variant`, `override`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, 
                `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, 
                `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, 
                `minimum`, `rating`, `sort_order`, `status`, `date_added`, `date_modified`, 
                `ncm`, `cest`
            ) VALUES (0, ?, '', '', '', '', '', '', '', '', '', 10, 5, '', NULL, 1, 0.0, 0, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, NOW(), NOW(), '', '')
        ");
        $stmt->execute([$modelA]);
        $idA = (int)$this->conn->lastInsertId();

        // Product B: quantity = 0, stock_status_id = 7
        $stmt->execute([$modelB]);
        $idB = (int)$this->conn->lastInsertId();
        $this->conn->prepare("UPDATE `" . DB_PREFIX . "product` SET quantity = 0, stock_status_id = 7 WHERE id = ?")->execute([$idB]);

        // Product C: quantity = 0, stock_status_id = 5 (Esgotado - should be hidden)
        $stmt->execute([$modelC]);
        $idC = (int)$this->conn->lastInsertId();
        $this->conn->prepare("UPDATE `" . DB_PREFIX . "product` SET quantity = 0, stock_status_id = 5 WHERE id = ?")->execute([$idC]);

        $stmtDesc = $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "product_description` (product_id, language_id, name, description) VALUES (?, 2, ?, '')");
        $stmtStore = $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "product_to_store` (product_id, store_id) VALUES (?, 1)");

        foreach ([$idA => 'Product A TEST-HIDE', $idB => 'Product B TEST-HIDE', $idC => 'Product C TEST-HIDE'] as $id => $name) {
            $stmtDesc->execute([$id, $name]);
            $stmtStore->execute([$id]);
        }

        $prodA = $mapper->getProduct($idA, 2, 1, 1);
        $this->assertNotEmpty($prodA, "Produto A (qty > 0) deve estar visível.");

        $prodB = $mapper->getProduct($idB, 2, 1, 1);
        $this->assertNotEmpty($prodB, "Produto B (status != 5) deve estar visível.");

        $prodC = $mapper->getProduct($idC, 2, 1, 1);
        $this->assertEmpty($prodC, "Produto C (qty = 0 e stock_status_id = 5) deve estar oculto.");

        // Cleanup
        foreach ([$idA, $idB, $idC] as $id) {
            $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE product_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "product_description` WHERE product_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM `" . DB_PREFIX . "product` WHERE id = ?")->execute([$id]);
        }
    }
}
