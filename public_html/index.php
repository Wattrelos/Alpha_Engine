<?php

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Alpha\Containers\AppContainer;
use Alpha\Controller\Actions\Main\HomeAction;
use Alpha\Services\Menu\CategoryMenuService;

require __DIR__ . '/../vendor/autoload.php';

// ─────────────────────────────────────────────────────────
// 1. BANCO DE DADOS — Credenciais carregadas do config.php principal
//    (apenas as constantes DB_* são usadas; o resto do config.php é ignorado aqui)
// ─────────────────────────────────────────────────────────
require_once __DIR__ . '/../config.php';

$pdo = new PDO(
    'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_DATABASE . ';port=' . DB_PORT . ';charset=utf8mb4',
    DB_USERNAME,
    DB_PASSWORD,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);

// ─────────────────────────────────────────────────────────
// 2. TWIG — Loader apontando para resources/views/
//    Os templates usam caminhos relativos a esta raiz:
//      'home.html.twig'
//      'layouts/base.html.twig'
//      'components/organisms/department-menu.twig'
//      'components/molecules/dropdown-recursive.twig'
//      'components/atoms/menu-button.twig'
// ─────────────────────────────────────────────────────────
$loader = new FilesystemLoader(__DIR__ . '/../resources/views');
$twig   = new Environment($loader, [
    'cache'       => __DIR__ . '/../storage/cache/twig_slim',
    'auto_reload' => true,
    'debug'       => true,
]);

// ─────────────────────────────────────────────────────────
// 3. SERVIÇOS — Instâncias que serão injetadas via AppContainer
// ─────────────────────────────────────────────────────────
$categoryMenu = new CategoryMenuService(
    pdo:        $pdo,
    languageId: 2,        // Português (pt-br)
    storeId:    0,        // Loja padrão
    prefix:     DB_PREFIX // 'tbkk_'
);

// ─────────────────────────────────────────────────────────
// 4. CONTAINER — Registra os serviços para auto-injeção via Reflection
// ─────────────────────────────────────────────────────────
$container = (new AppContainer())
    ->bind(Environment::class,        $twig)
    ->bind(CategoryMenuService::class, $categoryMenu);

// ─────────────────────────────────────────────────────────
// 5. SLIM APP
// ─────────────────────────────────────────────────────────
$app = AppFactory::create();
$app->addRoutingMiddleware();

// ─────────────────────────────────────────────────────────
// 6. ROTAS PÚBLICAS
// ─────────────────────────────────────────────────────────

// GET / → Página inicial com menu de categorias
$app->get('/', function ($request, $response) use ($container) {
    $action = $container->get(HomeAction::class);
    return $action($request, $response, []);
});

// ─────────────────────────────────────────────────────────
// 7. HANDLER GLOBAL DE ERROS
//    404 → Deve ser declarado DEPOIS das rotas.
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
