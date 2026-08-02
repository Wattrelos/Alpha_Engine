<?php

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Containers\AppBootstrap;
use Alpha\Mappers\EntityMappers\InformationMapper;
use Alpha\Auth\Middleware\LanguageMiddleware;
use Alpha\Auth\Middleware\LegacyRouteRedirectMiddleware;
use Alpha\Auth\Middleware\CsrfGuardMiddleware;
use Alpha\Auth\Middleware\InstallationCheckMiddleware;
use Alpha\Support\EnvironmentManager;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

$envManager = new EnvironmentManager(__DIR__ . '/../.env');
$isInstalled = $envManager->isInstalled();
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isSetupRoute = (str_starts_with($requestUri, '/setup') || str_starts_with($requestUri, '/install'));

// Redireciona caminhos do admin localizados (ex: /pt-br/LPDHED2dC7Gjrg2b/) de volta para o admin correto
if (preg_match('#^/(pt-br|en|es)/LPDHED2dC7Gjrg2b(/.*)?$#i', $requestUri, $matches)) {
    $remaining = $matches[2] ?? '';
    header('Location: /LPDHED2dC7Gjrg2b' . $remaining, true, 302);
    exit;
}

if (!defined('APPLICATION')) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_contains($uri, '/LPDHED2dC7Gjrg2b')) {
        define('APPLICATION', 'admin');
    } else {
        define('APPLICATION', 'catalog');
    }
}

// ─────────────────────────────────────────────────────────
// CONTROLE DE ESTADO DE INSTALAÇÃO (UNINSTALLED FLOW)
// ─────────────────────────────────────────────────────────
if (!$isInstalled) {
    if (!$isSetupRoute && !preg_match('/\.(png|jpg|jpeg|gif|css|js|ico|svg|woff|woff2|ttf|eot)$/i', $requestUri)) {
        header('Location: /setup', true, 302);
        exit;
    }

    require_once __DIR__ . '/../config.php';

    $app = AppFactory::create();
    $twigCacheDir = __DIR__ . '/../storage/cache/twig_setup';
    if (!is_dir($twigCacheDir)) {
        @mkdir($twigCacheDir, 0777, true);
    }
    $twig = Twig::create(__DIR__ . '/../resources/views', [
        'cache'       => false,
        'auto_reload' => true,
        'debug'       => true,
    ]);

    $app->add(TwigMiddleware::create($app, $twig));
    $app->addRoutingMiddleware();

    $app->get('/setup', \Alpha\Controller\Actions\Setup\ShowSetupAction::class);
    $app->post('/setup/test-db', \Alpha\Controller\Actions\Setup\TestDatabaseConnectionAction::class);
    $app->post('/setup/process', \Alpha\Controller\Actions\Setup\ProcessInstallationAction::class);

    $app->run();
    exit;
}

require_once __DIR__ . '/../config.php';

// ─────────────────────────────────────────────────────────
// 2. BOOTSTRAP DA ALPHA ENGINE (INSTALLED FLOW)
//    Inicializa dependências, registros, repositórios e serviços.
// ─────────────────────────────────────────────────────────
$bootstrap = AppBootstrap::boot();
$registry = $bootstrap->getRegistry();
$container = $bootstrap->getContainer();
$configSettings = $bootstrap->getConfigSettings();
$languageCode = $bootstrap->getLanguageCode();
$languageId = $bootstrap->getLanguageId();
$language = $bootstrap->getLanguage();
$categoryRepository = $bootstrap->getCategoryRepository();
$seoUrlRepository = $bootstrap->getSeoUrlRepository();
$languageRepository = $bootstrap->getLanguageRepository();
$informationRepository = $bootstrap->getInformationRepository();

// Detecta o modo de desenvolvimento/depuração com base no ambiente (.env)
$appEnv = $_ENV['APP_ENV'] ?? 'production';
$appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
$isDev = ($appEnv === 'development') && $appDebug;

// ─────────────────────────────────────────────────────────
// 3. TWIG — Loader e Instanciação via Slim Twig wrapper
// ─────────────────────────────────────────────────────────
$twigCacheDir = __DIR__ . '/../storage/cache/twig_slim';
if (!is_dir($twigCacheDir)) {
    @mkdir($twigCacheDir, 0777, true);
}
@chmod($twigCacheDir, 0777);

$twig = Twig::create(__DIR__ . '/../resources/views', [
    'cache'       => $twigCacheDir,
    'auto_reload' => $isDev,
    'debug'       => $isDev,
]);
$twigEnv = $twig->getEnvironment();
$twigEnv->addExtension(new \Alpha\Support\Twig\UrlExtension($seoUrlRepository));

// Resolve a URL completa do logotipo da loja
$logo = '';
if (!empty($configSettings['config_logo'])) {
    $logo = HTTP_SERVER . 'image/' . $configSettings['config_logo'];
}

// ── Twig Globals: disponíveis em TODOS os templates
$storeSettingsHelper = new \Alpha\Support\StoreSettings($configSettings, $languageId);
$storeData = $storeSettingsHelper->getFormattedSettings();

$twigEnv->addGlobal('categories', $categoryRepository->getMenuTree());
$twigEnv->addGlobal('settings',   $configSettings);
$twigEnv->addGlobal('name',       $storeData['name']);
$twigEnv->addGlobal('logo',       $storeData['logo']);
$twigEnv->addGlobal('store',      $storeData);
$twigEnv->addGlobal('home',       '/');
$twigEnv->addGlobal('lang',       $language ? $language->getCode() : 'pt-br');
$twigEnv->addGlobal('direction',  'ltr');

// Resolve as páginas institucionais para o rodapé usando URLs amigáveis
$mapperFactory = $registry->get('alpha_mapper_factory');
$informationMapper = $mapperFactory->get(InformationMapper::class);
$informations = [];
$rawInfos = $informationMapper->getInformations($languageId, 1);
$infoIds = array_column($rawInfos, 'id');
$seoUrlRepository->primeCache($infoIds, 'information_id', 1, $languageId);
foreach ($rawInfos as $result) {
    $keyword = $seoUrlRepository->getKeywordByQuery('information_id', (string)$result['id'], 1, $languageId);
    $informations[] = [
        'title' => $result['title'],
        'href'  => $keyword ? '/' . $languageCode . '/pagina/' . $keyword : '/' . $languageCode . '/pagina/' . $result['id']
    ];
}
$twigEnv->addGlobal('informations', $informations);

// ─────────────────────────────────────────────────────────
// 4. BIND TWIG NO CONTAINER DE DEPENDÊNCIAS
// ─────────────────────────────────────────────────────────
$container->bind(Environment::class, $twigEnv);
$container->bind(Twig::class, $twig);

// ─────────────────────────────────────────────────────────
// 5. SLIM APP
// ─────────────────────────────────────────────────────────
AppFactory::setContainer($container);
$app = AppFactory::create();

if (defined('APPLICATION') && APPLICATION === 'admin') {
    $app->setBasePath('/LPDHED2dC7Gjrg2b');
}

// Adiciona o Middleware do Twig para injetar as rotas de forma dinâmica
$app->add(TwigMiddleware::create($app, $twig));

// Adiciona o Middleware de Idioma para processar a variável {lang} após o roteador
$app->add(new LanguageMiddleware($languageRepository, $twigEnv, $registry));

// Adiciona a Proteção Anti-CSRF
$app->add(new CsrfGuardMiddleware($twigEnv));

// Adiciona os Cabeçalhos de Segurança HTTP (Security Headers)
$app->add(new SecurityHeadersMiddleware());

// Adiciona o Middleware de Estado de Instalação (Bloqueia re-instalação se já instalado)
$app->add(new InstallationCheckMiddleware($envManager));

$app->addRoutingMiddleware();

// Adiciona o Middleware de Redirecionamento de Rotas Legadas (executa primeiro)
$app->add(new LegacyRouteRedirectMiddleware($seoUrlRepository));

// ─────────────────────────────────────────────────────────
// 6. CARREGA ROTAS CENTRALIZADAS DA ALPHA ENGINE
// ─────────────────────────────────────────────────────────
$routes = require __DIR__ . '/../Config/Routes.php';
$routes($app);

// ─────────────────────────────────────────────────────────
// 8. HANDLERS GLOBAIS DE ERRO (404 & 500)
// ─────────────────────────────────────────────────────────
$errorMiddleware = $app->addErrorMiddleware($isDev, true, true);
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function ($request, $exception) use ($twigEnv) {
        $response = new \Slim\Psr7\Response();
        $html = $twigEnv->render('pages/errors/404.html.twig', [
            'title'       => 'Página Não Encontrada | AgSonhos',
            'description' => 'A página que você procura não existe.',
        ]);
        $response->getBody()->write($html);
        return $response->withStatus(404);
    }
);

if (!$isDev) {
    $errorMiddleware->setDefaultErrorHandler(
        function ($request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($twigEnv) {
            error_log("ALPHA 500 ERROR: " . $exception->getMessage() . "\n" . $exception->getTraceAsString());
            $response = new \Slim\Psr7\Response();

            $isXmlHttpRequest = strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
            $acceptsJson = str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json');

            if ($isXmlHttpRequest || $acceptsJson) {
                $response->getBody()->write((string)json_encode([
                    'error' => [
                        'warning' => 'Ocorreu um erro interno no servidor ao processar sua requisição.'
                    ]
                ], JSON_UNESCAPED_UNICODE));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(500);
            }

            try {
                $html = $twigEnv->render('pages/errors/500.html.twig', [
                    'title'       => 'Erro Interno no Servidor | AgSonhos',
                    'description' => 'Ocorreu um problema ao processar sua requisição.',
                ]);
            } catch (Throwable $e) {
                $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>500 - Erro Interno no Servidor</title></head><body><h1>500 - Erro Interno no Servidor</h1><p>Ocorreu um erro inesperado.</p></body></html>';
            }

            $response->getBody()->write($html);
            return $response->withStatus(500);
        }
    );
}

$app->run();
