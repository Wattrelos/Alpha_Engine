<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../backend/config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Alpha\Auth\Services\AdminAuthService;
use Alpha\Model\Domain\Repositories\UserRepository;

class LoggingValidationTest extends TestCase
{
    public function testFailedLoginAttemptLogsToAuditFile(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'catalog');
        }

        $bootstrap = AppBootstrap::boot();
        $container = $bootstrap->getContainer();
        $userRepo = $container->get('alpha_repository_factory')->get(UserRepository::class);
        $authService = new AdminAuthService($userRepo);

        $authService->authenticate('nonexistent_user', 'wrong_password', '192.168.0.1');

        $logFile = __DIR__ . '/../../backend/storage/logs/admin_login.log';
        $this->assertFileExists($logFile, "Arquivo de log admin_login.log deve ser criado em tentativas frustradas de login.");
        $content = file_get_contents($logFile);
        $this->assertStringContainsString('nonexistent_user', $content);
    }
}
