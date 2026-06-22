<?php
require_once __DIR__ . '/../vendor/autoload.php';
define('APPLICATION', 'catalog');
require_once __DIR__ . '/../config.php';

use Containers\AppBootstrap;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Mappers\EntityMappers\ProductMapper;

$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();
$conn = ConnectionDB::getInstance()->getConnection();
$mapper = new ProductMapper();

try {
    echo "=== 1. Setting up Test Products ===\n";

    // Common fields
    $modelA = 'TEST-HIDE-A';
    $modelB = 'TEST-HIDE-B';
    $modelC = 'TEST-HIDE-C';

    // Cleanup first just in case
    $conn->prepare("DELETE FROM `" . DB_PREFIX . "product` WHERE model IN (?, ?, ?)")->execute([$modelA, $modelB, $modelC]);

    // Product A: quantity = 10, stock_status_id = 5 (Esgotado) -> Should be shown (qty > 0)
    $stmt = $conn->prepare("
        INSERT INTO `" . DB_PREFIX . "product` (
            `master_id`, `model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, 
            `variant`, `override`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, 
            `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, 
            `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, 
            `minimum`, `rating`, `sort_order`, `status`, `date_added`, `date_modified`, 
            `ncm`, `cest`
        ) VALUES (
            0, ?, '', '', '', '', '', '', '', 
            '', '', 10, 5, '', NULL, 
            1, 0.0, 0, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), 0.00000000, 
            0, 0.00000000, 0.00000000, 0.00000000, 0, 1, 
            1, 0, 0, 1, NOW(), NOW(), 
            '', ''
        )
    ");
    $stmt->execute([$modelA]);
    $idA = (int)$conn->lastInsertId();

    // Product B: quantity = 0, stock_status_id = 7 (In Stock / default) -> Should be shown (status != 5)
    $stmt = $conn->prepare("
        INSERT INTO `" . DB_PREFIX . "product` (
            `master_id`, `model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, 
            `variant`, `override`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, 
            `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, 
            `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, 
            `minimum`, `rating`, `sort_order`, `status`, `date_added`, `date_modified`, 
            `ncm`, `cest`
        ) VALUES (
            0, ?, '', '', '', '', '', '', '', 
            '', '', 0, 7, '', NULL, 
            1, 0.0, 0, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), 0.00000000, 
            0, 0.00000000, 0.00000000, 0.00000000, 0, 1, 
            1, 0, 0, 1, NOW(), NOW(), 
            '', ''
        )
    ");
    $stmt->execute([$modelB]);
    $idB = (int)$conn->lastInsertId();

    // Product C: quantity = 0, stock_status_id = 5 (Esgotado) -> Should be HIDDEN
    $stmt = $conn->prepare("
        INSERT INTO `" . DB_PREFIX . "product` (
            `master_id`, `model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, 
            `variant`, `override`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, 
            `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, 
            `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, 
            `minimum`, `rating`, `sort_order`, `status`, `date_added`, `date_modified`, 
            `ncm`, `cest`
        ) VALUES (
            0, ?, '', '', '', '', '', '', '', 
            '', '', 0, 5, '', NULL, 
            1, 0.0, 0, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), 0.00000000, 
            0, 0.00000000, 0.00000000, 0.00000000, 0, 1, 
            1, 0, 0, 1, NOW(), NOW(), 
            '', ''
        )
    ");
    $stmt->execute([$modelC]);
    $idC = (int)$conn->lastInsertId();

    // Insert descriptions and store mappings
    $stmtDesc = $conn->prepare("INSERT INTO `" . DB_PREFIX . "product_description` (product_id, language_id, name, description) VALUES (?, ?, ?, '')");
    $stmtStore = $conn->prepare("INSERT INTO `" . DB_PREFIX . "product_to_store` (product_id, store_id) VALUES (?, ?)");

    foreach ([$idA => 'Product A TEST-HIDE', $idB => 'Product B TEST-HIDE', $idC => 'Product C TEST-HIDE'] as $id => $name) {
        $stmtDesc->execute([$id, 2, $name]); // language_id = 2
        $stmtStore->execute([$id, 1]);       // store_id = 1
    }

    echo "Inserted Test Products IDs: A: $idA, B: $idB, C: $idC\n";

    echo "\n=== 2. Testing getProduct for individual visibility ===\n";

    // Test Product A (qty > 0, status = 5) -> Should be retrieved
    $prodA = $mapper->getProduct($idA, 2, 1, 1);
    if (empty($prodA)) {
        throw new \Exception("Product A should be visible!");
    }
    echo "Assertion PASSED: Product A is visible.\n";

    // Test Product B (qty = 0, status = 7) -> Should be retrieved
    $prodB = $mapper->getProduct($idB, 2, 1, 1);
    if (empty($prodB)) {
        throw new \Exception("Product B should be visible!");
    }
    echo "Assertion PASSED: Product B is visible.\n";

    // Test Product C (qty = 0, status = 5) -> Should be hidden
    $prodC = $mapper->getProduct($idC, 2, 1, 1);
    if (!empty($prodC)) {
        throw new \Exception("Product C should be hidden!");
    }
    echo "Assertion PASSED: Product C is hidden.\n";

    echo "\n=== 3. Testing getProducts list filtering ===\n";
    $products = $mapper->getProducts(['filter_name' => 'TEST-HIDE'], 2, 1, 1);
    $foundIds = array_column($products, 'id');
    
    echo "Found product IDs: " . implode(', ', $foundIds) . "\n";
    if (!in_array($idA, $foundIds)) {
        throw new \Exception("getProducts: Product A missing from list!");
    }
    if (!in_array($idB, $foundIds)) {
        throw new \Exception("getProducts: Product B missing from list!");
    }
    if (in_array($idC, $foundIds)) {
        throw new \Exception("getProducts: Product C should not be in list!");
    }
    echo "Assertion PASSED: getProducts list filters correctly.\n";

    echo "\n=== 4. Cleaning Up ===\n";
    foreach ([$idA, $idB, $idC] as $id) {
        $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE product_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_description` WHERE product_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM `" . DB_PREFIX . "product` WHERE id = ?")->execute([$id]);
    }
    echo "Cleaned up successfully.\n";
    echo "\n=== ALL STOCK STATUS HIDING TESTS PASSED SUCCESSFULLY! ===\n";

} catch (\Throwable $e) {
    echo "\n❌ TEST FAILED!\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
