<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../backend/config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Twig\Environment;
use Slim\Psr7\Factory\ServerRequestFactory;
use Alpha\Auth\Middleware\AdminLanguageMiddleware;
use Alpha\Admin\Controllers\Actions\Common\SwitchAdminLanguageAction;
use Alpha\Support\Language;

class AdminLanguageValidationTest extends TestCase
{
    private $app;
    private $container;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'admin');
        }

        $bootstrap = AppBootstrap::boot();
        $this->container = $bootstrap->getContainer();

        $twig = Twig::create(__DIR__ . '/../../backend/resources/views', [
            'cache'       => false,
            'auto_reload' => true,
            'debug'       => true,
        ]);
        $twigEnv = $twig->getEnvironment();
        $this->container->bind(Environment::class, $twigEnv);
        $this->container->bind(Twig::class, $twig);

        AppFactory::setContainer($this->container);
        $app = AppFactory::create();

        $app->get('/test-dashboard', function($request, $response) use ($twig) {
            return $twig->render($response, 'admin/layouts/base.html.twig');
        })->add(new AdminLanguageMiddleware(
            $this->container->get(\Alpha\Model\Domain\Repositories\LanguageRepository::class),
            $twigEnv,
            $this->container
        ));

        $app->get('/test-supplier-list', function($request, $response) use ($twig) {
            return $twig->render($response, 'admin/catalog/supplier/index.html.twig', [
                'suppliers' => [],
                'filters'   => ['filter_name' => '', 'filter_tax_id' => ''],
                'total'     => 0,
                'limit'     => 10
            ]);
        })->setName('admin.supplier.list')->add(new AdminLanguageMiddleware(
            $this->container->get(\Alpha\Model\Domain\Repositories\LanguageRepository::class),
            $twigEnv,
            $this->container
        ));

        $app->get('/test-product-list', function($request, $response) use ($twig) {
            return $twig->render($response, 'admin/pages/products/list.html.twig', [
                'products' => [],
                'categories' => [],
                'manufacturers' => [],
                'filters'   => ['filter_name' => '', 'filter_ean' => '', 'filter_category_id' => '', 'filter_manufacturer_id' => '', 'filter_status' => ''],
                'total'     => 0,
                'limit'     => 10
            ]);
        })->setName('admin.product.list')->add(new AdminLanguageMiddleware(
            $this->container->get(\Alpha\Model\Domain\Repositories\LanguageRepository::class),
            $twigEnv,
            $this->container
        ));

        $app->post('/test-switch', SwitchAdminLanguageAction::class);

        $this->app = $app;
    }

    public function testDefaultLanguageFallbackToPortuguese(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest('GET', '/test-dashboard');
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        $this->assertStringContainsString('Painel Inicial', $body);
        $this->assertStringContainsString('Sair do Painel', $body);
    }

    public function testLanguageSwitchCookieHeader(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest('POST', '/test-switch')
            ->withParsedBody(['language_code' => 'en-gb']);

        $response = $this->app->handle($request);
        $cookieHeaders = $response->getHeader('Set-Cookie');

        $foundCookie = false;
        foreach ($cookieHeaders as $header) {
            if (str_contains($header, 'admin_language=en-gb')) {
                $foundCookie = true;
                break;
            }
        }
        $this->assertTrue($foundCookie, "Set-Cookie header deve conter 'admin_language=en-gb'.");
    }

    public function testEnglishTranslationLoadViaCookie(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest('GET', '/test-dashboard')
            ->withCookieParams(['admin_language' => 'en-gb']);

        $response = $this->app->handle($request);
        $body = (string)$response->getBody();

        $this->assertStringContainsString('Dashboard', $body);
        $this->assertStringContainsString('Logout', $body);
    }

    public function testFrenchTranslationLoadViaCookie(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest('GET', '/test-dashboard')
            ->withCookieParams(['admin_language' => 'fr-fr']);

        $response = $this->app->handle($request);
        $body = (string)$response->getBody();

        $this->assertStringContainsString('Tableau de Bord', $body);
        $this->assertStringContainsString('Se Déconnecter', $body);
    }

    public function testTranslationClassAndDryFallback(): void
    {
        $translator = new Language('pt-br');
        $flatCommon = $translator->load('common');
        $this->assertNotEmpty($flatCommon);
        $this->assertNotNull($translator->get('button.back', 'common'));

        // DRY fallback from home to common
        $translator->load('home');
        $this->assertEquals($translator->get('button.back', 'common'), $translator->get('button.back', 'home'));

        // Language switch
        $translator->setCode('en-gb');
        $this->assertEquals('en-gb', $translator->getCode());
    }
}
