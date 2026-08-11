<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../config.php';

use PHPUnit\Framework\TestCase;
use Alpha\Support\EnvironmentManager;
use Alpha\Auth\Middleware\InstallationCheckMiddleware;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class TenantProvisioningValidationTest extends TestCase
{
    private string $testEnvFile;

    protected function setUp(): void
    {
        $this->testEnvFile = __DIR__ . '/../../storage/cache/test_app_validation.env';
        if (file_exists($this->testEnvFile)) {
            @unlink($this->testEnvFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testEnvFile)) {
            @unlink($this->testEnvFile);
        }
    }

    public function testSetupActionsExist(): void
    {
        $this->assertTrue(class_exists(\Alpha\Admin\Controllers\Actions\Auth\ShowSetupAction::class));
        $this->assertTrue(class_exists(\Alpha\Admin\Controllers\Actions\Auth\SetupAction::class));
    }

    public function testEnvironmentManagerReadingAndAtomicUpdate(): void
    {
        $backupEnv = $_ENV['APP_INSTALLED'] ?? null;
        unset($_ENV['APP_INSTALLED']);
        putenv('APP_INSTALLED');

        $envManager = new EnvironmentManager($this->testEnvFile);
        $this->assertFalse($envManager->isInstalled());

        $key1 = EnvironmentManager::generateRandomKey(32);
        $updateResult = $envManager->updateEnv([
            'APP_ENV'        => 'development',
            'APP_INSTALLED'  => 'true',
            'DB_HOSTNAME'    => '127.0.0.1',
            'JWT_SECRET_KEY' => $key1,
        ]);

        $this->assertTrue($updateResult);
        $this->assertFileExists($this->testEnvFile);

        $parsed = $envManager->readEnv();
        $this->assertEquals('true', $parsed['APP_INSTALLED'] ?? '');
        $this->assertEquals($key1, $parsed['JWT_SECRET_KEY'] ?? '');

        if ($backupEnv !== null) {
            $_ENV['APP_INSTALLED'] = $backupEnv;
            putenv("APP_INSTALLED={$backupEnv}");
        }
    }

    public function testInstallationCheckMiddlewareBlockingAndRedirecting(): void
    {
        $envManager = new EnvironmentManager($this->testEnvFile);
        $envManager->updateEnv([
            'APP_INSTALLED' => 'true'
        ]);

        $middleware = new InstallationCheckMiddleware($envManager);
        $requestFactory = new ServerRequestFactory();
        $request = $requestFactory->createServerRequest('GET', '/setup');

        $dummyHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response {
                $res = new Response();
                $res->getBody()->write('OK');
                return $res->withStatus(200);
            }
        };

        $response = $middleware->process($request, $dummyHandler);
        $this->assertEquals(403, $response->getStatusCode(), "Acesso a /setup deve ser bloqueado com 403 quando APP_INSTALLED=true.");

        // Uninstalled case
        @unlink($this->testEnvFile);
        unset($_ENV['APP_INSTALLED']);
        putenv('APP_INSTALLED');

        $envManagerUninstalled = new EnvironmentManager($this->testEnvFile);
        $middlewareUninstalled = new InstallationCheckMiddleware($envManagerUninstalled);

        $requestHome = $requestFactory->createServerRequest('GET', '/');
        $responseHome = $middlewareUninstalled->process($requestHome, $dummyHandler);

        $this->assertEquals(302, $responseHome->getStatusCode());
        $this->assertEquals('/setup', $responseHome->getHeaderLine('Location'));
    }
}
