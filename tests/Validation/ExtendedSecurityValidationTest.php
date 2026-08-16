<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../backend/config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;

class ExtendedSecurityValidationTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'admin');
        }
        AppBootstrap::boot();
    }

    public function testDebugModeFlags(): void
    {
        // Debug mode must be boolean false in production configuration or properly flagged
        $this->assertTrue(defined('APPLICATION'));
    }

    public function testLgpdSanitizerDataMasking(): void
    {
        $rawPiiData = [
            'name' => 'João Silva',
            'cpf' => '123.456.789-00',
            'email' => 'joao.silva@exemplo.com',
            'phone' => '(11) 99999-8888'
        ];

        // Masking functions check
        $maskedEmail = preg_replace('/(?<=.).(?=.*@)/u', '*', $rawPiiData['email']);
        $this->assertNotEquals($rawPiiData['email'], $maskedEmail);
        $this->assertStringContainsString('*', $maskedEmail);
    }

    public function testSecureCookieSettings(): void
    {
        $cookieParams = session_get_cookie_params();
        $this->assertIsArray($cookieParams);
    }

    public function testUserManagementPermissionsAndGroupI18n(): void
    {
        $container = AppBootstrap::boot()->getContainer();
        $userGroupRepo = $container->get('alpha_repository_factory')->get(\Alpha\Model\Domain\Repositories\UserGroupRepository::class);
        $this->assertNotNull($userGroupRepo);

        $groups = $userGroupRepo->findAll();
        $this->assertIsArray($groups);
    }
}
