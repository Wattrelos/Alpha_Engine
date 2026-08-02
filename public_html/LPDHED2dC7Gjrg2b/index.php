<?php

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Containers\AppBootstrap;

require __DIR__ . '/../../vendor/autoload.php';

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();
}

if (!defined('APPLICATION')) {
    define('APPLICATION', 'admin');
}

// 1. BANCO DE DADOS E DIRETÓRIOS
require_once __DIR__ . '/../../config.php';

// 2. BOOTSTRAP DA ALPHA ENGINE
$bootstrap = AppBootstrap::boot();
$registry = $bootstrap->getRegistry();
$container = $bootstrap->getContainer();
$configSettings = $bootstrap->getConfigSettings();
$languageCode = $bootstrap->getLanguageCode();
$languageId = $bootstrap->getLanguageId();
$language = $bootstrap->getLanguage();
$seoUrlRepository = $bootstrap->getSeoUrlRepository();

$appEnv = $_ENV['APP_ENV'] ?? 'production';
$appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
$isDev = ($appEnv === 'development') && $appDebug;

// 3. TWIG WRAPPER
$twigCacheDir = __DIR__ . '/../../storage/cache/twig_slim';
if (!is_dir($twigCacheDir)) {
    @mkdir($twigCacheDir, 0777, true);
}
@chmod($twigCacheDir, 0777);

$twig = Twig::create(__DIR__ . '/../../resources/views', [
    'cache'       => $twigCacheDir,
    'auto_reload' => $isDev,
    'debug'       => $isDev,
]);
$twigEnv = $twig->getEnvironment();
$twigEnv->addExtension(new \Alpha\Support\Twig\UrlExtension($seoUrlRepository));

// Globais para o Admin
$twigEnv->addGlobal('settings',   $configSettings);
$twigEnv->addGlobal('name',       $configSettings['config_name'] ?? 'AG Sonhos e Construções');
$twigEnv->addGlobal('lang',       $language ? $language->getCode() : 'pt-br');
$twigEnv->addGlobal('admin_dir',  defined('ADMIN_DIR') ? ADMIN_DIR : basename(__DIR__));
$twigEnv->addGlobal('admin_path', defined('ADMIN_PATH') ? ADMIN_PATH : '/' . basename(__DIR__));

// 4. BIND NO CONTAINER
$container->bind(Environment::class, $twigEnv);
$container->bind(Twig::class, $twig);

// 5. SLIM APP
AppFactory::setContainer($container);
$app = AppFactory::create();

// Define o BasePath para o roteamento funcionar relativo ao diretório oculto do admin
$app->setBasePath('/' . (defined('ADMIN_DIR') ? ADMIN_DIR : basename(__DIR__)));

use Alpha\Auth\Middleware\CsrfGuardMiddleware;
use Alpha\Auth\Middleware\SecurityHeadersMiddleware;

$app->add(TwigMiddleware::create($app, $twig));
$app->add(new CsrfGuardMiddleware($twigEnv));
$app->add(new SecurityHeadersMiddleware());
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware($isDev, true, true);

if (!$isDev) {
    $errorMiddleware->setDefaultErrorHandler(
        function ($request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($twigEnv) {
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
                $html = $twigEnv->render('admin/pages/errors/500.html.twig', [
                    'message' => 'Ocorreu um problema interno no servidor ao processar a operação administrativa.'
                ]);
            } catch (Throwable $e) {
                $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>500 - Erro Interno no Servidor</title></head><body><h1>500 - Erro Interno no Servidor</h1><p>Ocorreu um erro inesperado.</p></body></html>';
            }

            $response->getBody()->write($html);
            return $response->withStatus(500);
        }
    );
}

// 6. CARREGA ROTAS
$routes = require __DIR__ . '/../../Config/Routes.php';
$routes($app);

$app->run();
