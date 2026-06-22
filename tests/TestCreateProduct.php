<?php
require_once __DIR__ . '/../vendor/autoload.php';
define('APPLICATION', 'admin');
require_once __DIR__ . '/../config.php';

use Containers\AppBootstrap;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Twig\Environment;
use Alpha\Model\DataAccessObject\ConnectionDB;

$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();

// Real Twig Environment instance
$twig = Twig::create(__DIR__ . '/../resources/views', [
    'cache'       => false,
    'auto_reload' => true,
    'debug'       => true,
]);
$twigEnv = $twig->getEnvironment();
$container->bind(Environment::class, $twigEnv);
$container->bind(Twig::class, $twig);

AppFactory::setContainer($container);
$app = AppFactory::create();

// Define route
$app->map(['GET', 'POST'], '/produtos/criar', \Alpha\Admin\Controllers\Actions\Catalog\Product\CreateProductAction::class);

$conn = ConnectionDB::getInstance()->getConnection();

try {
    echo "=== 1. Testing GET Request (Render Form) ===\n";
    $serverRequestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();
    $requestGet = $serverRequestFactory->createServerRequest('GET', '/produtos/criar');

    // Simulate middleware setting languageId and storeId on action (base controller takes care of it, usually from session)
    // For test simplicity, we run the request through the app:
    $responseGet = $app->handle($requestGet);

    echo "GET Response status: " . $responseGet->getStatusCode() . "\n";
    $body = (string)$responseGet->getBody();
    echo "Body length: " . strlen($body) . "\n";

    if ($responseGet->getStatusCode() !== 200) {
        throw new \Exception("Expected status 200 for GET, got " . $responseGet->getStatusCode());
    }
    if (!str_contains($body, 'name="name"') || !str_contains($body, 'name="model"')) {
        throw new \Exception("HTML form missing required input fields.");
    }
    echo "Assertion PASSED: GET request returned the correct form HTML.\n";

    echo "\n=== 2. Testing POST Request with Invalid Data ===\n";
    $requestPostInvalid = $serverRequestFactory->createServerRequest('POST', '/produtos/criar')
        ->withParsedBody(['model' => 'TEST-MODEL']); // missing 'name'

    $responsePostInvalid = $app->handle($requestPostInvalid);
    echo "POST Invalid Response status: " . $responsePostInvalid->getStatusCode() . "\n";
    echo "POST Invalid Response body: " . (string)$responsePostInvalid->getBody() . "\n";
    if ($responsePostInvalid->getStatusCode() !== 400) {
        throw new \Exception("Expected status 400 for invalid post data, got " . $responsePostInvalid->getStatusCode());
    }
    echo "Assertion PASSED: Empty name validation rejected.\n";

    echo "\n=== 3. Testing POST Request with Valid Data ===\n";
    $testProductName = 'Test Product Action ' . time();
    $testProductModel = 'TEST-MODEL-' . time();
    $testProductPrice = 99.99;
    $testProductQuantity = 100;

    // Find or create a test category
    $testCategoryId = (int)$conn->query("SELECT id FROM `" . DB_PREFIX . "category` LIMIT 1")->fetchColumn();
    $createdTestCategory = false;
    if (!$testCategoryId) {
        $conn->prepare("INSERT INTO `" . DB_PREFIX . "category` (`parent_id`, `sort_order`, `status`, `date_added`, `date_modified`) VALUES (0, 0, 1, NOW(), NOW())")->execute();
        $testCategoryId = (int)$conn->lastInsertId();
        $conn->prepare("INSERT INTO `" . DB_PREFIX . "category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, 1, 'Test Cat', '', '', '', '')")->execute([$testCategoryId]);
        $createdTestCategory = true;
    }

    $requestPostValid = $serverRequestFactory->createServerRequest('POST', '/produtos/criar')
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

    $responsePostValid = $app->handle($requestPostValid);
    echo "POST Valid Response status: " . $responsePostValid->getStatusCode() . "\n";
    if ($responsePostValid->getStatusCode() !== 302) {
        throw new \Exception("Expected 302 redirect on success, got " . $responsePostValid->getStatusCode());
    }

    // Check database to ensure insertion was correct
    $stmt = $conn->prepare("
        SELECT p.id, pd.name, pd.description, p.model, p.price, p.quantity, p.status 
        FROM `" . DB_PREFIX . "product` p
        LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.id = pd.product_id
        WHERE p.model = ?
    ");
    $stmt->execute([$testProductModel]);
    $insertedProduct = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$insertedProduct) {
        throw new \Exception("Product was not inserted into database!");
    }

    echo "Inserted Product ID: " . $insertedProduct['id'] . "\n";
    echo "Inserted Product Name: " . $insertedProduct['name'] . "\n";
    echo "Inserted Product Model: " . $insertedProduct['model'] . "\n";

    if ($insertedProduct['name'] !== $testProductName) {
        throw new \Exception("Name mismatch: " . $insertedProduct['name']);
    }

    // Check product_to_store insertion
    $stmtStore = $conn->prepare("SELECT store_id FROM `" . DB_PREFIX . "product_to_store` WHERE product_id = ?");
    $stmtStore->execute([$insertedProduct['id']]);
    $storeId = $stmtStore->fetchColumn();
    if ($storeId != 1) {
        throw new \Exception("Product was not linked to store_id = 1, got store_id: " . $storeId);
    }

    // Check product_to_category insertion
    $stmtCat = $conn->prepare("SELECT category_id FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = ?");
    $stmtCat->execute([$insertedProduct['id']]);
    $insertedCatId = (int)$stmtCat->fetchColumn();
    if ($insertedCatId !== $testCategoryId) {
        throw new \Exception("Product was not linked to category_id = " . $testCategoryId . ", got: " . $insertedCatId);
    }

    echo "Assertion PASSED: Product successfully inserted and associated in store & category.\n";

    echo "\n=== 4. Cleaning Up ===\n";
    $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = ?")->execute([$insertedProduct['id']]);
    $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE product_id = ?")->execute([$insertedProduct['id']]);
    $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_description` WHERE product_id = ?")->execute([$insertedProduct['id']]);
    $conn->prepare("DELETE FROM `" . DB_PREFIX . "product` WHERE id = ?")->execute([$insertedProduct['id']]);
    if ($createdTestCategory) {
        $conn->prepare("DELETE FROM `" . DB_PREFIX . "category_description` WHERE category_id = ?")->execute([$testCategoryId]);
        $conn->prepare("DELETE FROM `" . DB_PREFIX . "category` WHERE id = ?")->execute([$testCategoryId]);
    }
    echo "Test product and dummy categories cleaned up successfully.\n";

    echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
} catch (\Throwable $e) {
    echo "\n❌ TEST FAILED!\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
