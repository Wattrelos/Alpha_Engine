<?php

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Containers\AppContainer;
use Alpha\Controller\Actions\Main\HomeAction;
use Alpha\Controller\Actions\Customer\Auth\ShowLoginFormAction;
use Alpha\Controller\Actions\Customer\Auth\LoginAction;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Auth\Services\AuthService;
use Alpha\Mappers\MapperFactory;
use Alpha\Mappers\EntityMappers\InformationMapper;
use Alpha\Support\Registry;
use Alpha\Controller\Actions\Customer\Auth\ShowRegistrationFormAction;
use Alpha\Controller\Actions\Customer\Auth\RegisterAction;
use Alpha\Controller\Actions\Customer\Auth\LogoutAction;
use Alpha\Session\AlphaSessionHandler;
use Alpha\Model\Domain\Repositories\SessionRepository;
use Alpha\Controller\Actions\Product\ShowProductAction;
use Alpha\Controller\Actions\Category\ShowCategoryAction;
use Alpha\Controller\Actions\Information\ShowInformationAction;
use Alpha\Controller\Actions\Product\SearchProductsAction;
use Alpha\Auth\Middleware\LanguageMiddleware;
use Alpha\Auth\Middleware\LegacyRouteRedirectMiddleware;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Alpha\Model\Domain\Repositories\InformationRepository;

require __DIR__ . '/../vendor/autoload.php';

// ─────────────────────────────────────────────────────────
// 1. BANCO DE DADOS
//    config.php define apenas as constantes DB_* e DIR_*.
//    Não inicializa o framework do OpenCart — apenas defines.
// ─────────────────────────────────────────────────────────
require_once __DIR__ . '/../config.php';

if (!defined('APPLICATION')) {
    define('APPLICATION', 'catalog');
}

// O autoloader do Composer (carregado acima) resolve todas as classes nativas e legadas.

// require_once DIR_SYSTEM . 'helper/general.php';
// require_once DIR_SYSTEM . 'helper/filter.php';
// require_once DIR_SYSTEM . 'helper/validation.php';

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4',
        DB_HOSTNAME,
        DB_DATABASE,
        DB_PORT
    ),
    DB_USERNAME,
    DB_PASSWORD,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);

// ─────────────────────────────────────────────────────────
// 2. REPOSITÓRIOS — Instâncias que alimentam os serviços
//    Usando o novo Slim\CategoryRepository (sem OpenCart\Registry)
// ─────────────────────────────────────────────────────────
$registry = new Registry();
$mapperFactory = new MapperFactory($registry);
$repositoryFactory = new RepositoryFactory($mapperFactory, $registry);

// Registrar as factories no registry para uso por outros componentes
$registry->set('alpha_mapper_factory', $mapperFactory);
$registry->set('alpha_repository_factory', $repositoryFactory);

$settingRepository = new SettingRepository($mapperFactory, $registry);
$languageRepository = new LanguageRepository($mapperFactory, $registry);
$customerRepository = new CustomerRepository($mapperFactory, $registry);
$authService = new AuthService($customerRepository);

$sessionRepository = new SessionRepository($mapperFactory, $registry);
$sessionHandler = new AlphaSessionHandler($sessionRepository);
session_set_save_handler($sessionHandler, true);

// Resolvendo dinamicamente as configurações e idioma da loja
$configSettings = $settingRepository->getSetting('config', 0);
$languageCode   = $configSettings['config_language_catalog'] ?? 'pt-br';
$language       = $languageRepository->getByCode($languageCode);

if (!$language) {
    $language = $languageRepository->find(2); // Fallback para pt-br (ID 2)
}

$languageId = $language ? $language->getId() : 2;

// Injetando adaptadores e serviços standalone no Registry para compatibilidade com repositórios legados
$registry->set('config', new \Alpha\Support\Config(array_merge([
    'config_customer_group_id' => 1,
    'config_tax' => false,
    'config_customer_price' => false,
    'config_language' => $languageCode,
    'config_language_id' => $languageId,
], $configSettings)));

$languageAdaptor = new \Alpha\Support\Language($languageCode);
$registry->set('language', $languageAdaptor);
$registry->set('session', new \Alpha\Support\Session());
$registry->set('customer', new \Alpha\Support\Customer());
$registry->set('tax', new \Alpha\Support\Tax());
$registry->set('currency', new \Alpha\Support\Currency($languageAdaptor));
$registry->set('url', new \Alpha\Support\Url());
$registry->set('document', new \Alpha\Support\Document());

$categoryRepository = new CategoryRepository($mapperFactory, $registry);
$productRepository = $repositoryFactory->get(ProductRepository::class);
$seoUrlRepository  = $repositoryFactory->get(SeoUrlRepository::class);
$informationRepository = $repositoryFactory->get(InformationRepository::class);

// ─────────────────────────────────────────────────────────
// 3. TWIG — Loader apontando para resources/views/
// ─────────────────────────────────────────────────────────
$loader = new FilesystemLoader(__DIR__ . '/../resources/views');
$twig   = new Environment($loader, [
    'cache'       => __DIR__ . '/../storage/cache/twig_slim',
    'auto_reload' => true,
    'debug'       => true,
]);
$twig->addExtension(new \Alpha\Support\Twig\UrlExtension($seoUrlRepository));

// Resolve a URL completa do logotipo da loja
$logo = '';
if (!empty($configSettings['config_logo'])) {
    $logo = HTTP_SERVER . 'img/' . $configSettings['config_logo'];
}

// ── Twig Globals: disponíveis em TODOS os templates
$twig->addGlobal('categories', $categoryRepository->getMenuTree());
$twig->addGlobal('settings',   $configSettings);
$twig->addGlobal('name',       $configSettings['config_name'] ?? 'AG Sonhos e Construções');
$twig->addGlobal('logo',       $logo);
$twig->addGlobal('home',       '/');
$twig->addGlobal('lang',       $language ? $language->getCode() : 'pt-br');
$twig->addGlobal('direction',  'ltr');

// Resolve as páginas institucionais para o rodapé usando URLs amigáveis
$informationMapper = $mapperFactory->get(InformationMapper::class);
$informations = [];
foreach ($informationMapper->getInformations($languageId, 0) as $result) {
    $keyword = $seoUrlRepository->getKeywordByQuery('information_id', (string)$result['id'], 0, $languageId);
    $informations[] = [
        'title' => $result['title'],
        'href'  => $keyword ? '/' . $languageCode . '/pagina/' . $keyword : '/' . $languageCode . '/pagina/' . $result['id']
    ];
}
$twig->addGlobal('informations', $informations);

// ─────────────────────────────────────────────────────────
// 4. CONTAINER DE DEPENDÊNCIAS
//    Binds: tipo → instância. AppContainer injeta via Reflection.
// ─────────────────────────────────────────────────────────
$container = (new AppContainer())
    ->bind(Environment::class,      $twig)
    ->bind(CategoryRepository::class, $categoryRepository)
    ->bind(SettingRepository::class, $settingRepository)
    ->bind(LanguageRepository::class, $languageRepository)
    ->bind(CustomerRepository::class, $customerRepository)
    ->bind(AuthService::class,        $authService)
    ->bind(SessionRepository::class,  $sessionRepository)
    ->bind(ProductRepository::class,  $productRepository)
    ->bind(SeoUrlRepository::class,   $seoUrlRepository)
    ->bind(InformationRepository::class, $informationRepository);

// ─────────────────────────────────────────────────────────
// 5. SLIM APP
// ─────────────────────────────────────────────────────────
$app = AppFactory::create();

// Adiciona o Middleware de Idioma para processar a variável {lang} após o roteador
$app->add(new LanguageMiddleware($languageRepository, $twig, $registry));

$app->addRoutingMiddleware();

// Adiciona o Middleware de Redirecionamento de Rotas Legadas (executa primeiro)
$app->add(new LegacyRouteRedirectMiddleware($seoUrlRepository));

// ─────────────────────────────────────────────────────────
// 6. REDIRECIONAMENTOS DE COMPATIBILIDADE / FALLBACKS DE IDIOMA
// ─────────────────────────────────────────────────────────

// Redireciona a raiz "/" para o idioma padrão
$app->get('/', function ($request, $response) {
    return $response->withHeader('Location', '/pt-br')->withStatus(302);
});

// Redirecionamentos de conveniência para URLs sem idioma
$app->get('/login', function ($request, $response) {
    return $response->withHeader('Location', '/pt-br/login')->withStatus(302);
});
$app->get('/cadastro', function ($request, $response) {
    return $response->withHeader('Location', '/pt-br/cadastro')->withStatus(302);
});
$app->get('/logout', function ($request, $response) {
    return $response->withHeader('Location', '/pt-br/logout')->withStatus(302);
});
$app->get('/carrinho', function ($request, $response) {
    return $response->withHeader('Location', '/pt-br/carrinho')->withStatus(302);
});
$app->get('/checkout', function ($request, $response) {
    return $response->withHeader('Location', '/pt-br/checkout')->withStatus(302);
});
$app->get('/busca', function ($request, $response) {
    $queryParams = $request->getQueryParams();
    $searchQuery = isset($queryParams['busca']) ? '?busca=' . urlencode($queryParams['busca']) : '';
    return $response->withHeader('Location', '/pt-br/busca' . $searchQuery)->withStatus(302);
});

// ─────────────────────────────────────────────────────────
// 7. GRUPO DE ROTAS INTERNACIONALIZADAS
// ─────────────────────────────────────────────────────────
$app->group('/{lang:pt-br|en|es}', function (\Slim\Routing\RouteCollectorProxy $group) use ($container) {
    
    // Página Inicial do Idioma
    $group->get('', function ($request, $response, $args) use ($container) {
        return $container->get(HomeAction::class)($request, $response, $args);
    });

    // Login
    $group->get('/login', function ($request, $response, $args) use ($container) {
        return $container->get(ShowLoginFormAction::class)($request, $response, $args);
    });
    $group->post('/login', function ($request, $response, $args) use ($container) {
        return $container->get(LoginAction::class)($request, $response, $args);
    });

    // Cadastro
    $group->get('/cadastro', function ($request, $response, $args) use ($container) {
        return $container->get(ShowRegistrationFormAction::class)($request, $response, $args);
    });
    $group->post('/cadastro', function ($request, $response, $args) use ($container) {
        return $container->get(RegisterAction::class)($request, $response, $args);
    });

    // Logout
    $group->get('/logout', function ($request, $response, $args) use ($container) {
        return $container->get(LogoutAction::class)($request, $response, $args);
    });

    // Detalhe do Produto (SEO)
    $group->get('/produto/{slug}', function ($request, $response, $args) use ($container) {
        return $container->get(ShowProductAction::class)($request, $response, $args);
    });

    // Listagem da Categoria (SEO)
    $group->get('/categoria/{slug}', function ($request, $response, $args) use ($container) {
        return $container->get(ShowCategoryAction::class)($request, $response, $args);
    });

    // Página Institucional (SEO)
    $group->get('/pagina/{slug}', function ($request, $response, $args) use ($container) {
        return $container->get(ShowInformationAction::class)($request, $response, $args);
    });

    // Busca de Produtos
    $group->get('/busca', function ($request, $response, $args) use ($container) {
        return $container->get(SearchProductsAction::class)($request, $response, $args);
    });
});

// ─────────────────────────────────────────────────────────
// 8. HANDLER GLOBAL DE 404
// ─────────────────────────────────────────────────────────
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function ($request, $exception) use ($twig) {
        $response = new \Slim\Psr7\Response();
        $html = $twig->render('pages/errors/404.html.twig', [
            'title'       => 'Página Não Encontrada | AgSonhos',
            'description' => 'A página que você procura não existe.',
        ]);
        $response->getBody()->write($html);
        return $response->withStatus(404);
    }
);

$app->run();
