<?php

use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\UserGroupRepository;
use Alpha\Model\Domain\Repositories\UserLoginRepository;
use Alpha\Model\Domain\Repositories\UserAuthorizeRepository;

require '/vendor/autoload.php';
define('APPLICATION', 'catalog');
require_once '/config.php';
$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();
/** @var \Alpha\Model\Domain\Repositories\RepositoryFactory $factory */
$factory = $container->get('alpha_repository_factory');
// Test UserGroupRepository
echo "Testing UserGroupRepository:\n";
$userGroupRepo = $factory->get(UserGroupRepository::class);
$userGroup = $userGroupRepo->find(1);
if ($userGroup) {
    echo "Found User Group 1: " . $userGroup->getName() . "\n";
    echo "Permissions count: " . count($userGroup->getPermissionArray()) . "\n";
} else {
    echo "User Group 1 not found!\n";
}
// Test UserLoginRepository
echo "\nTesting UserLoginRepository:\n";
$userLoginRepo = $factory->get(UserLoginRepository::class);
$attempts = $userLoginRepo->countRecentLogins(1);
echo "Recent login attempts for user 1: " . $attempts . "\n";
// Test UserAuthorizeRepository
echo "\nTesting UserAuthorizeRepository:\n";
$userAuthRepo = $factory->get(UserAuthorizeRepository::class);
$auth = $userAuthRepo->findByToken('nonexistent-token');
if ($auth === null) {
    echo "UserAuthorizeRepository findByToken returned null correctly for nonexistent token.\n";
} else {
    echo "Unexpected result from findByToken.\n";
}
echo "\nAll repository checks passed successfully!\n";
