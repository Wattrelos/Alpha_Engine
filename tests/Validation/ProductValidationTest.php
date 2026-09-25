<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../backend/config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Twig\Environment;
use Slim\Psr7\Factory\ServerRequestFactory;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

class ProductValidationTest extends TestCase
{
    private $app;
    private $conn;
    private $container;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'admin');
        }

        $bootstrap = AppBootstrap::boot();
        $this->container = $bootstrap->getContainer();
        $this->conn = ConnectionDB::getInstance()->getConnection();

        $twig = Twig::create(__DIR__ . '/../../backend/resources/views', [
            'cache'       => false,
            'auto_reload' => true,
            'debug'       => true,
        ]);
        $this->container->bind(Environment::class, $twig->getEnvironment());
        $this->container->bind(Twig::class, $twig);

        AppFactory::setContainer($this->container);
        $app = AppFactory::create();
        $app->map(['GET', 'POST'], '/produtos/criar', \Alpha\Admin\Controllers\Actions\Catalog\Product\CreateProductAction::class);
        $app->get('/produtos/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Catalog\Product\EditProductAction::class);
        $app->post('/produtos/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Catalog\Product\UpdateProductAction::class);

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
        $this->assertStringContainsString('data-tab="tab-general"', $body);
        $this->assertStringContainsString('data-tab="tab-data"', $body);
        $this->assertStringContainsString('data-tab="tab-dimensions"', $body);
        $this->assertStringContainsString('data-tab="tab-fiscal"', $body);
        $this->assertStringContainsString('data-tab="tab-seo"', $body);
        $this->assertStringContainsString('name="ncm"', $body);
        $this->assertStringContainsString('name="cest"', $body);
        $this->assertStringContainsString('name="weight"', $body);
        $this->assertStringContainsString('name="length"', $body);
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
            $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "category` (`parent_id`, `sort_order`, `status`) VALUES (NULL, 0, 1)")->execute();
            $testCategoryId = (int)$this->conn->lastInsertId();
            $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, 1, 'Test Cat', '', '', '', '')")->execute([$testCategoryId]);
            $createdTestCategory = true;
        }

        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest('POST', '/produtos/criar')
            ->withParsedBody([
                'name' => $testProductName,
                'model' => $testProductModel,
                'sku' => 'SKU-ACTION-TEST',
                'price' => $testProductPrice,
                'quantity' => $testProductQuantity,
                'status' => 1,
                'description' => 'Detailed test description here.',
                'tag' => 'tag1, tag2',
                'meta_title' => 'Meta Title Action',
                'meta_description' => 'Meta Description Action',
                'meta_keyword' => 'kw1, kw2',
                'ean' => '1234567890123',
                'upc' => '123456789012',
                'mpn' => 'MPN-ACTION-01',
                'ncm' => '6907.21.00',
                'cest' => '10.001.00',
                'weight' => 12.5000,
                'weight_class_id' => 1,
                'length' => 30.00,
                'width' => 20.00,
                'height' => 15.00,
                'length_class_id' => 1,
                'shipping' => 1,
                'minimum' => 2,
                'subtract' => 1,
                'stock_status_id' => 7,
                'manufacturer_id' => 0,
                'date_available' => date('Y-m-d'),
                'product_category' => [$testCategoryId]
            ]);

        $response = $this->app->handle($request);
        $this->assertEquals(302, $response->getStatusCode());

        $stmt = $this->conn->prepare("
            SELECT p.*, pd.name, pd.tag, pd.meta_title, pd.meta_description, pd.meta_keyword
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.id = pd.product_id AND pd.language_id = 2)
            WHERE p.model = ?
        ");
        $stmt->execute([$testProductModel]);
        $insertedProduct = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($insertedProduct, "Produto inserido deve ser encontrado no banco.");
        $this->assertEquals($testProductName, $insertedProduct['name']);
        $this->assertEquals('SKU-ACTION-TEST', $insertedProduct['sku']);
        $this->assertEquals('6907.21.00', $insertedProduct['ncm']);
        $this->assertEquals('10.001.00', $insertedProduct['cest']);
        $this->assertEquals('12.50000000', $insertedProduct['weight']);
        $this->assertEquals('30.00000000', $insertedProduct['length']);
        $this->assertEquals('20.00000000', $insertedProduct['width']);
        $this->assertEquals('15.00000000', $insertedProduct['height']);
        $this->assertEquals('Meta Title Action', $insertedProduct['meta_title']);
        $this->assertEquals('tag1, tag2', $insertedProduct['tag']);

        // Test Edit Form Render
        $productId = (int)$insertedProduct['id'];
        $editRequest = $serverRequestFactory->createServerRequest('GET', "/produtos/{$productId}/editar");
        $editResponse = $this->app->handle($editRequest);
        $this->assertEquals(200, $editResponse->getStatusCode());
        $editBody = (string)$editResponse->getBody();
        $this->assertStringContainsString('data-tab="tab-general"', $editBody);
        $this->assertStringContainsString('data-tab="tab-data"', $editBody);
        $this->assertStringContainsString('data-tab="tab-dimensions"', $editBody);
        $this->assertStringContainsString('data-tab="tab-fiscal"', $editBody);
        $this->assertStringContainsString('data-tab="tab-seo"', $editBody);
        $this->assertStringContainsString('data-tab="tab-variants"', $editBody);
        $this->assertStringContainsString('value="6907.21.00"', $editBody);

        // Test Update Persistence
        $updateRequest = $serverRequestFactory->createServerRequest('POST', "/produtos/{$productId}/editar")
            ->withParsedBody([
                'name' => $testProductName . ' Updated',
                'model' => $testProductModel,
                'sku' => 'SKU-ACTION-UPDATED',
                'price' => 149.90,
                'quantity' => 50,
                'status' => 1,
                'description' => 'Updated desc',
                'tag' => 'updated_tag',
                'meta_title' => 'Updated Meta Title',
                'meta_description' => 'Updated Meta Desc',
                'meta_keyword' => 'up_kw',
                'ean' => '9876543210987',
                'ncm' => '6907.22.00',
                'cest' => '10.002.00',
                'weight' => 25.0000,
                'weight_class_id' => 1,
                'length' => 45.00,
                'width' => 35.00,
                'height' => 25.00,
                'length_class_id' => 1,
                'shipping' => 1,
                'minimum' => 1,
                'subtract' => 1,
                'stock_status_id' => 7,
                'manufacturer_id' => 0,
                'date_available' => date('Y-m-d'),
                'product_category' => [$testCategoryId]
            ]);
        $updateResponse = $this->app->handle($updateRequest);
        $this->assertEquals(302, $updateResponse->getStatusCode());

        $stmt->execute([$testProductModel]);
        $updatedProduct = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals($testProductName . ' Updated', $updatedProduct['name']);
        $this->assertEquals('SKU-ACTION-UPDATED', $updatedProduct['sku']);
        $this->assertEquals('6907.22.00', $updatedProduct['ncm']);
        $this->assertEquals('10.002.00', $updatedProduct['cest']);
        $this->assertEquals('25.00000000', $updatedProduct['weight']);
        $this->assertEquals('45.00000000', $updatedProduct['length']);
        $this->assertEquals('Updated Meta Title', $updatedProduct['meta_title']);

        // Cleanup
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
