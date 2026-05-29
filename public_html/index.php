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

$categoryRepository = new CategoryRepository($mapperFactory, $registry);

// ─────────────────────────────────────────────────────────
// 3. TWIG — Loader apontando para resources/views/
// ─────────────────────────────────────────────────────────
$loader = new FilesystemLoader(__DIR__ . '/../resources/views');
$twig   = new Environment($loader, [
    'cache'       => __DIR__ . '/../storage/cache/twig_slim',
    'auto_reload' => true,
    'debug'       => true,
]);

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

// Resolve as páginas institucionais para o rodapé
$informationMapper = $mapperFactory->get(InformationMapper::class);
$informations = [];
foreach ($informationMapper->getInformations($languageId, 0) as $result) {
    $informations[] = [
        'title' => $result['title'],
        'href'  => '/index.php?route=information/information&language=' . $languageCode . '&information_id=' . $result['id']
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
    ->bind(SessionRepository::class,  $sessionRepository);

// ─────────────────────────────────────────────────────────
// 5. SLIM APP
// ─────────────────────────────────────────────────────────
$app = AppFactory::create();
$app->addRoutingMiddleware();

// ─────────────────────────────────────────────────────────
// 6. ROTAS WEB (sem SessionMiddleware — rotas públicas)
// ─────────────────────────────────────────────────────────

// GET / → Home page com grade de categorias
$app->get('/', function ($request, $response) use ($container) {
    return $container->get(HomeAction::class)($request, $response, []);
});

// GET /login → Página de login
$app->get('/login', function ($request, $response) use ($container) {
    return $container->get(ShowLoginFormAction::class)($request, $response, []);
});

// POST /login → Processamento do login
$app->post('/login', function ($request, $response) use ($container) {
    return $container->get(LoginAction::class)($request, $response, []);
});
// GET /cadastro → Ir para a página de cadastro
$app->get('/cadastro', function ($request, $response) use ($container) {
    return $container->get(ShowRegistrationFormAction::class)($request, $response, []);
});
// POST /cadastro → Processamento do cadastro
$app->post('/cadastro', function ($request, $response) use ($container) {
    return $container->get(RegisterAction::class)($request, $response, []);
});

// GET /logout → Encerramento da sessão
$app->get('/logout', function ($request, $response) use ($container) {
    return $container->get(LogoutAction::class)($request, $response, []);
});


// ─────────────────────────────────────────────────────────
// 7. HANDLER GLOBAL DE 404
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
