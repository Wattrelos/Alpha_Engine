<?php
require_once __DIR__ . '/../vendor/autoload.php';
define('APPLICATION', 'admin');
require_once __DIR__ . '/../config.php';

use Containers\AppBootstrap;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Twig\Environment;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Auth\Middleware\AdminLanguageMiddleware;
use Alpha\Admin\Controllers\Actions\Common\SwitchAdminLanguageAction;

$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();

// Real Twig Environment instance
$twig = Twig::create(__DIR__ . '/../resources/views', [
    'cache'       => false,
    'auto_reload' => true,
    'debug'       => true,
]);
$twigEnv = $twig->getEnvironment();
$container->bind(Environment::class, $twigEnv);
$container->bind(Twig::class, $twig);

AppFactory::setContainer($container);
$app = AppFactory::create();

// Add test routes
$app->get('/test-dashboard', function($request, $response) use ($twig) {
    return $twig->render($response, 'admin/layouts/base.html.twig');
})->add(new AdminLanguageMiddleware(
    $container->get(\Alpha\Model\Domain\Repositories\LanguageRepository::class),
    $twigEnv,
    $container
));

$app->get('/test-supplier-list', function($request, $response) use ($twig) {
    return $twig->render($response, 'admin/catalog/supplier/index.html.twig', [
        'suppliers' => [],
        'filters'   => ['filter_name' => '', 'filter_tax_id' => ''],
        'total'     => 0,
        'limit'     => 10
    ]);
})->setName('admin.supplier.list')->add(new AdminLanguageMiddleware(
    $container->get(\Alpha\Model\Domain\Repositories\LanguageRepository::class),
    $twigEnv,
    $container
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
    $container->get(\Alpha\Model\Domain\Repositories\LanguageRepository::class),
    $twigEnv,
    $container
));

$app->post('/test-switch', SwitchAdminLanguageAction::class);

try {
    echo "=== STARTING ADMIN LANGUAGE INTEGRATION TEST ===\n\n";

    $serverRequestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();

    echo "=== 1. Testing Default Language (Portuguese) ===\n";
    $requestGetPt = $serverRequestFactory->createServerRequest('GET', '/test-dashboard');
    $responseGetPt = $app->handle($requestGetPt);

    $bodyPt = (string)$responseGetPt->getBody();
    echo "Status: " . $responseGetPt->getStatusCode() . "\n";
    
    // Assertions for Portuguese
    if (!str_contains($bodyPt, 'Painel Inicial') || !str_contains($bodyPt, 'Sair do Painel')) {
        throw new \Exception("Default language did not fall back to Portuguese correctly.");
    }
    echo "Assertion PASSED: Default language is Portuguese (found 'Painel Inicial', 'Sair do Painel').\n";

    echo "\n=== 2. Testing Language Switch Request (Switch to English) ===\n";
    $requestPostSwitch = $serverRequestFactory->createServerRequest('POST', '/test-switch')
        ->withParsedBody(['language_code' => 'en-gb']);

    $responsePostSwitch = $app->handle($requestPostSwitch);
    echo "Status: " . $responsePostSwitch->getStatusCode() . "\n";
    
    $cookieHeaders = $responsePostSwitch->getHeader('Set-Cookie');
    echo "Set-Cookie Header: " . implode(', ', $cookieHeaders) . "\n";

    $foundCookie = false;
    foreach ($cookieHeaders as $header) {
        if (str_contains($header, 'admin_language=en-gb')) {
            $foundCookie = true;
            break;
        }
    }
    
    if (!$foundCookie) {
        throw new \Exception("Language switch did not set the correct admin_language cookie header.");
    }
    echo "Assertion PASSED: Set-Cookie header contains 'admin_language=en-gb'.\n";

    echo "\n=== 3. Testing English Translation Load via Cookie ===\n";
    $requestGetEn = $serverRequestFactory->createServerRequest('GET', '/test-dashboard')
        ->withCookieParams(['admin_language' => 'en-gb']);

    $responseGetEn = $app->handle($requestGetEn);
    $bodyEn = (string)$responseGetEn->getBody();

    // Assertions for English
    if (!str_contains($bodyEn, 'Dashboard') || !str_contains($bodyEn, 'Logout')) {
        throw new \Exception("English language translations were not loaded correctly.");
    }
    echo "Assertion PASSED: English translation successfully loaded (found 'Dashboard', 'Logout').\n";

    echo "\n=== 4. Testing French Translation Load via Cookie ===\n";
    $requestGetFr = $serverRequestFactory->createServerRequest('GET', '/test-dashboard')
        ->withCookieParams(['admin_language' => 'fr-fr']);

    $responseGetFr = $app->handle($requestGetFr);
    $bodyFr = (string)$responseGetFr->getBody();

    // Assertions for French
    if (!str_contains($bodyFr, 'Tableau de Bord') || !str_contains($bodyFr, 'Se Déconnecter')) {
        throw new \Exception("French language translations were not loaded correctly.");
    }
    echo "Assertion PASSED: French translation successfully loaded (found 'Tableau de Bord', 'Se Déconnecter').\n";

    echo "\n=== 5. Testing Supplier List Translation Load (Portuguese) ===\n";
    $requestGetPtSuppliers = $serverRequestFactory->createServerRequest('GET', '/test-supplier-list');
    $responseGetPtSuppliers = $app->handle($requestGetPtSuppliers);
    $bodyPtSuppliers = (string)$responseGetPtSuppliers->getBody();
    if (!str_contains($bodyPtSuppliers, 'Gerenciamento de Fornecedores') || !str_contains($bodyPtSuppliers, 'Razão Social')) {
        throw new \Exception("Supplier list default language did not load Portuguese correctly.");
    }
    echo "Assertion PASSED: Supplier list default language loaded Portuguese correctly (found 'Gerenciamento de Fornecedores', 'Razão Social').\n";

    echo "\n=== 6. Testing Supplier List Translation Load (English) ===\n";
    $requestGetEnSuppliers = $serverRequestFactory->createServerRequest('GET', '/test-supplier-list')
        ->withCookieParams(['admin_language' => 'en-gb']);
    $responseGetEnSuppliers = $app->handle($requestGetEnSuppliers);
    $bodyEnSuppliers = (string)$responseGetEnSuppliers->getBody();
    if (!str_contains($bodyEnSuppliers, 'Supplier Management') || !str_contains($bodyEnSuppliers, 'Company Name')) {
        throw new \Exception("Supplier list language did not load English correctly.");
    }
    echo "Assertion PASSED: Supplier list language loaded English correctly (found 'Supplier Management', 'Company Name').\n";

    echo "\n=== 7. Testing Supplier List Translation Load (French) ===\n";
    $requestGetFrSuppliers = $serverRequestFactory->createServerRequest('GET', '/test-supplier-list')
        ->withCookieParams(['admin_language' => 'fr-fr']);
    $responseGetFrSuppliers = $app->handle($requestGetFrSuppliers);
    $bodyFrSuppliers = (string)$responseGetFrSuppliers->getBody();
    if (!str_contains($bodyFrSuppliers, 'Gestion des Fournisseurs') || !str_contains($bodyFrSuppliers, 'Raison Sociale')) {
        throw new \Exception("Supplier list language did not load French correctly.");
    }
    echo "Assertion PASSED: Supplier list language loaded French correctly (found 'Gestion des Fournisseurs', 'Raison Sociale').\n";

    echo "\n=== 8. Testing Product List Translation Load (Portuguese) ===\n";
    $requestGetPtProducts = $serverRequestFactory->createServerRequest('GET', '/test-product-list');
    $responseGetPtProducts = $app->handle($requestGetPtProducts);
    $bodyPtProducts = (string)$responseGetPtProducts->getBody();
    if (!str_contains($bodyPtProducts, 'Gerenciamento de Produtos') || !str_contains($bodyPtProducts, 'Nome do Produto')) {
        throw new \Exception("Product list default language did not load Portuguese correctly.");
    }
    echo "Assertion PASSED: Product list default language loaded Portuguese correctly (found 'Gerenciamento de Produtos', 'Nome do Produto').\n";

    echo "\n=== 9. Testing Product List Translation Load (English) ===\n";
    $requestGetEnProducts = $serverRequestFactory->createServerRequest('GET', '/test-product-list')
        ->withCookieParams(['admin_language' => 'en-gb']);
    $responseGetEnProducts = $app->handle($requestGetEnProducts);
    $bodyEnProducts = (string)$responseGetEnProducts->getBody();
    if (!str_contains($bodyEnProducts, 'Product Management') || !str_contains($bodyEnProducts, 'Product Name')) {
        throw new \Exception("Product list language did not load English correctly.");
    }
    echo "Assertion PASSED: Product list language loaded English correctly (found 'Product Management', 'Product Name').\n";

    echo "\n=== 10. Testing Product List Translation Load (French) ===\n";
    $requestGetFrProducts = $serverRequestFactory->createServerRequest('GET', '/test-product-list')
        ->withCookieParams(['admin_language' => 'fr-fr']);
    $responseGetFrProducts = $app->handle($requestGetFrProducts);
    $bodyFrProducts = (string)$responseGetFrProducts->getBody();
    if (!str_contains($bodyFrProducts, 'Gestion des Produits') || !str_contains($bodyFrProducts, 'Nom du Produit')) {
        throw new \Exception("Product list language did not load French correctly.");
    }
    echo "Assertion PASSED: Product list language loaded French correctly (found 'Gestion des Produits', 'Nom du Produit').\n";

    echo "\n=== ALL ADMIN LANGUAGE TESTS PASSED SUCCESSFULLY! ===\n";
} catch (\Throwable $e) {
    echo "\n❌ TEST FAILED!\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
