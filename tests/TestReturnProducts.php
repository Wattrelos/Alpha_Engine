<?php
require_once '/var/www/html/agsonhos/vendor/autoload.php';
require_once '/var/www/html/agsonhos/config.php';
$bootstrap = Containers\AppBootstrap::boot();
$container = $bootstrap->getContainer();
// Real Twig Environment instance
$loader = new \Twig\Loader\ArrayLoader();
$twig = new \Twig\Environment($loader);
$container->bind(\Twig\Environment::class, $twig);
try {
    $action = $container->get(\Alpha\Controller\Actions\Customer\Account\AddReturnAction::class);
    echo "AddReturnAction resolved successfully: " . get_class($action) . "\n";
} catch (\Throwable $e) {
    echo "AddReturnAction Error:\n";
    echo $e . "\n";
}
try {
    $action = $container->get(\Alpha\Controller\Actions\Customer\Account\ShowReturnAction::class);
    echo "ShowReturnAction resolved successfully: " . get_class($action) . "\n";
} catch (\Throwable $e) {
    echo "ShowReturnAction Error:\n";
    echo $e . "\n";
}
try {
    $action = $container->get(\Alpha\Admin\Controllers\Actions\Sales\Return\ListReturnsAction::class);
    echo "Admin ListReturnsAction resolved successfully: " . get_class($action) . "\n";
} catch (\Throwable $e) {
    echo "Admin ListReturnsAction Error:\n";
    echo $e . "\n";
}
try {
    $action = $container->get(\Alpha\Admin\Controllers\Actions\Sales\Return\ShowReturnAction::class);
    echo "Admin ShowReturnAction resolved successfully: " . get_class($action) . "\n";
} catch (\Throwable $e) {
    echo "Admin ShowReturnAction Error:\n";
    echo $e . "\n";
}
try {
    $action = $container->get(\Alpha\Admin\Controllers\Actions\Sales\Return\UpdateReturnStatusAction::class);
    echo "Admin UpdateReturnStatusAction resolved successfully: " . get_class($action) . "\n";
} catch (\Throwable $e) {
    echo "Admin UpdateReturnStatusAction Error:\n";
    echo $e . "\n";
}
