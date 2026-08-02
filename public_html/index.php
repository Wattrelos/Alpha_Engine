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

// ─────────────────────────────────────────────────────────
// 0. VERIFICAÇÃO E AUTO-INSTALAÇÃO DE DEPENDÊNCIAS (COMPOSER)
// ─────────────────────────────────────────────────────────
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    if (function_exists('exec')) {
        @exec('composer install --no-interaction --optimize-autoloader 2>&1', $output, $returnCode);
    }

    if (!file_exists($autoloadPath)) {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(503);
        echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dependências Ausentes | Alpha Engine</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0c0f17; color: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { background: rgba(22, 27, 38, 0.9); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 40px; max-width: 540px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.6); text-align: center; }
        .icon { font-size: 3rem; margin-bottom: 16px; }
        h1 { font-size: 1.4rem; color: #ff6b00; margin-bottom: 12px; font-weight: 700; }
        p { color: #9ca3af; font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px; }
        .code-box { background: #000; border: 1px solid #333; border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 1rem; color: #10b981; text-align: center; margin-bottom: 20px; font-weight: bold; }
        .btn-reload { background: #ff6b00; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.2s; }
        .btn-reload:hover { background: #e05d00; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">📦</div>
        <h1>Dependências do Composer Ausentes</h1>
        <p>A pasta <code>vendor/</code> não foi encontrada. Execute o comando abaixo no terminal da pasta do projeto para carregar os componentes:</p>
        <div class="code-box">composer install</div>
        <p style="font-size: 0.8rem; color: #6b7280;">Após rodar o comando, recarregue a página para acessar o assistente de instalação.</p>
        <a href="" onclick="window.location.reload(); return false;" class="btn-reload">Recarregar Página</a>
    </div>
</body>
</html>';
        exit;
    }
}

require $autoloadPath;

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

    $setupContainer = new \Containers\AppContainer();
    $twigCacheDir = __DIR__ . '/../storage/cache/twig_setup';
    if (!is_dir($twigCacheDir)) {
        @mkdir($twigCacheDir, 0777, true);
    }
    $twig = Twig::create(__DIR__ . '/../resources/views', [
        'cache'       => false,
        'auto_reload' => true,
        'debug'       => true,
    ]);

    $setupContainer->bind(Environment::class, $twig->getEnvironment());
    $setupContainer->bind(Twig::class, $twig);
    $setupContainer->bind(EnvironmentManager::class, $envManager);
    $setupContainer->bind(\Alpha\Controller\Actions\Setup\ShowSetupAction::class, new \Alpha\Controller\Actions\Setup\ShowSetupAction($twig->getEnvironment(), $envManager));
    $setupContainer->bind(\Alpha\Controller\Actions\Setup\TestDatabaseConnectionAction::class, new \Alpha\Controller\Actions\Setup\TestDatabaseConnectionAction());
    $setupContainer->bind(\Alpha\Controller\Actions\Setup\ProcessInstallationAction::class, new \Alpha\Controller\Actions\Setup\ProcessInstallationAction($envManager));

    AppFactory::setContainer($setupContainer);
    $app = AppFactory::create();

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
