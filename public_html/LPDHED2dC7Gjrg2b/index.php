<?php

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Containers\AppBootstrap;

require __DIR__ . '/../../vendor/autoload.php';

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

// 3. TWIG WRAPPER
$twig = Twig::create(__DIR__ . '/../../resources/views', [
    'cache'       => __DIR__ . '/../../storage/cache/twig_slim',
    'auto_reload' => true,
    'debug'       => true,
]);
$twigEnv = $twig->getEnvironment();
$twigEnv->addExtension(new \Alpha\Support\Twig\UrlExtension($seoUrlRepository));

// Globais para o Admin
$twigEnv->addGlobal('settings',   $configSettings);
$twigEnv->addGlobal('name',       $configSettings['config_name'] ?? 'AG Sonhos e Construções');
$twigEnv->addGlobal('lang',       $language ? $language->getCode() : 'pt-br');

// 4. BIND NO CONTAINER
$container->bind(Environment::class, $twigEnv);
$container->bind(Twig::class, $twig);

// 5. SLIM APP
AppFactory::setContainer($container);
$app = AppFactory::create();

// Define o BasePath para o roteamento funcionar relativo ao diretório oculto do admin
$app->setBasePath('/LPDHED2dC7Gjrg2b');

$app->add(TwigMiddleware::create($app, $twig));
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// 6. CARREGA ROTAS
$routes = require __DIR__ . '/../../Config/Routes.php';
$routes($app);

$app->run();
