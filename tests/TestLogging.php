<?php

use Containers\AppBootstrap;
use Alpha\Auth\Services\AdminAuthService;
use Alpha\Model\Domain\Repositories\UserRepository;

require '/var/www/html/agsonhos/vendor/autoload.php';
define('APPLICATION', 'catalog');
require_once '/var/www/html/agsonhos/config.php';
$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();
$userRepo = $container->get('alpha_repository_factory')->get(UserRepository::class);
$authService = new AdminAuthService($userRepo);
echo "Simulating a failed login attempt for 'nonexistent_user':\n";
$authService->authenticate('nonexistent_user', 'wrong_password', '192.168.0.1');
$logFile = '/var/www/html/agsonhos/storage/logs/admin_login.log';
if (file_exists($logFile)) {
    echo "\nLog file content:\n";
    echo file_get_contents($logFile);
} else {
    echo "\nLog file was not created!\n";
}
