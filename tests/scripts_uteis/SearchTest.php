<?php
require_once __DIR__ . '/../../vendor/autoload.php';
define('APPLICATION', 'admin');
require_once __DIR__ . '/../../config.php';

use Containers\AppBootstrap;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Twig\Environment;
use Alpha\Model\DataAccessObject\ConnectionDB;

$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();

$twig = Twig::create(__DIR__ . '/../../resources/views', [
    'cache'       => false,
    'auto_reload' => true,
    'debug'       => true,
]);
$twigEnv = $twig->getEnvironment();
$container->bind(Environment::class, $twigEnv);
$container->bind(Twig::class, $twig);

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->get('/produtos/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Catalog\Product\EditProductAction::class);

$conn = ConnectionDB::getInstance()->getConnection();
$stmt = $conn->query("SELECT id FROM " . DB_PREFIX . "product LIMIT 1");
$prod = $stmt->fetch();
$id = $prod ? $prod['id'] : 0;

$serverRequestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();
$request = $serverRequestFactory->createServerRequest('GET', "/produtos/{$id}/editar");

try {
    $response = $app->handle($request);
    echo "SUCCESS\n";
    echo "Status code: " . $response->getStatusCode() . "\n";
    echo "Body length: " . strlen((string)$response->getBody()) . "\n";
    echo "Contains EAN field: " . (str_contains((string)$response->getBody(), 'name="ean"') ? 'YES' : 'NO') . "\n";
    echo "Contains stock_status_id field: " . (str_contains((string)$response->getBody(), 'name="stock_status_id"') ? 'YES' : 'NO') . "\n";
    echo "Contains manufacturer_id field: " . (str_contains((string)$response->getBody(), 'name="manufacturer_id"') ? 'YES' : 'NO') . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
