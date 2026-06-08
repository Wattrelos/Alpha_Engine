<?php
require 'vendor/autoload.php';
// Define DB config constants
define('DB_PREFIX', 'agsc_');
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', '127.0.0.1');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '42010052');
define('DB_DATABASE', 'AlphaAgsonhos');
define('DB_PORT', '3306');
$mapperFactory = new \Alpha\Mappers\MapperFactory();
$container = new \Containers\AppContainer([]);
$repositoryFactory = new \Alpha\Model\Domain\Repositories\RepositoryFactory($mapperFactory, $container);
$productRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\ProductRepository::class);
$product = $productRepo->getProduct(13);
echo "Parsed Description:\n";
echo $product['description'] . "\n";
