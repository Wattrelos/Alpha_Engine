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

require __DIR__ . '/../vendor/autoload.php';

// Redireciona caminhos do admin localizados (ex: /pt-br/LPDHED2dC7Gjrg2b/) de volta para o admin correto
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (preg_match('#^/(pt-br|en|es)/LPDHED2dC7Gjrg2b(/.*)?$#i', $requestUri, $matches)) {
    $remaining = $matches[2] ?? '';
    header('Location: /LPDHED2dC7Gjrg2b' . $remaining, true, 302);
    exit;
}

// ─────────────────────────────────────────────────────────
// 1. BANCO DE DADOS
//    config.php define apenas as constantes DB_* e DIR_*.
//    Não inicializa o frameworkdo código legado — apenas defines.
// ─────────────────────────────────────────────────────────
if (!defined('APPLICATION')) {
    define('APPLICATION', 'catalog');
}
require_once __DIR__ . '/../config.php';

// ─────────────────────────────────────────────────────────
// 2. BOOTSTRAP DA ALPHA ENGINE
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

// ─────────────────────────────────────────────────────────
// 3. TWIG — Loader e Instanciação via Slim Twig wrapper
// ─────────────────────────────────────────────────────────
$twig = Twig::create(__DIR__ . '/../resources/views', [
    'cache'       => __DIR__ . '/../storage/cache/twig_slim',
    'auto_reload' => true,
    'debug'       => true,
]);
$twigEnv = $twig->getEnvironment();
$twigEnv->addExtension(new \Alpha\Support\Twig\UrlExtension($seoUrlRepository));

// Resolve a URL completa do logotipo da loja
$logo = '';
if (!empty($configSettings['config_logo'])) {
    $logo = HTTP_SERVER . 'img/' . $configSettings['config_logo'];
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

// Adiciona o Middleware do Twig para injetar as rotas de forma dinâmica
$app->add(TwigMiddleware::create($app, $twig));

// Adiciona o Middleware de Idioma para processar a variável {lang} após o roteador
$app->add(new LanguageMiddleware($languageRepository, $twigEnv, $registry));

$app->addRoutingMiddleware();

// Adiciona o Middleware de Redirecionamento de Rotas Legadas (executa primeiro)
$app->add(new LegacyRouteRedirectMiddleware($seoUrlRepository));

// ─────────────────────────────────────────────────────────
// 6. CARREGA ROTAS CENTRALIZADAS DA ALPHA ENGINE
// ─────────────────────────────────────────────────────────
$routes = require __DIR__ . '/../Config/Routes.php';
$routes($app);

// ─────────────────────────────────────────────────────────
// 8. HANDLER GLOBAL DE 404
// ─────────────────────────────────────────────────────────
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
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

$app->run();
