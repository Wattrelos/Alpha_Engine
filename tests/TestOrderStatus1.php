<?php

use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\OrderStatusRepository;

require '/var/www/html/agsonhos/vendor/autoload.php';

define('APPLICATION', 'catalog');
require_once '/var/www/html/agsonhos/config.php';

$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();

/** @var \Alpha\Model\Domain\Repositories\RepositoryFactory $factory */
$factory = $container->get('alpha_repository_factory');

/** @var OrderStatusRepository $repo */
$repo = $factory->get(OrderStatusRepository::class);

echo "Fetching all order statuses:\n";
$statuses = $repo->getOrderStatuses();
print_r($statuses);

echo "\nFetching order status by ID 1:\n";
$status = $repo->getOrderStatus(1);
print_r($status);

echo "\nUsing BaseRepositoryInterface find(1):\n";
$statusEntity = $repo->find(1);
if ($statusEntity) {
    echo "Found status entity: ID=" . $statusEntity->getId() . ", Name=" . $statusEntity->getName() . "\n";
} else {
    echo "Status entity not found!\n";
}
