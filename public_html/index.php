<?php

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Containers\AppBootstrap;
use Alpha\Controller\Actions\Main\HomeAction;
use Alpha\Controller\Actions\Customer\Auth\ShowLoginFormAction;
use Alpha\Controller\Actions\Customer\Auth\LoginAction;
use Alpha\Mappers\EntityMappers\InformationMapper;
use Alpha\Controller\Actions\Customer\Auth\ShowRegistrationFormAction;
use Alpha\Controller\Actions\Customer\Auth\RegisterAction;
use Alpha\Controller\Actions\Customer\Auth\LogoutAction;
use Alpha\Controller\Actions\Customer\Auth\AccountAction;
use Alpha\Controller\Actions\Customer\OrderHistoryAction;
use Alpha\Controller\Actions\Product\ShowProductAction;
use Alpha\Controller\Actions\Category\ShowCategoryAction;
use Alpha\Controller\Actions\Information\ShowInformationAction;
use Alpha\Controller\Actions\Information\ShowSitemapAction;
use Alpha\Controller\Actions\Product\SearchProductsAction;
use Alpha\Auth\Middleware\LanguageMiddleware;
use Alpha\Auth\Middleware\LegacyRouteRedirectMiddleware;
use Alpha\Controller\Actions\Customer\OrdersAction;
use Alpha\Controller\Actions\Cart\ShowCartAction;
use Alpha\Controller\Actions\Cart\AddCartAction;
use Alpha\Controller\Actions\Cart\EditCartAction;
use Alpha\Controller\Actions\Cart\RemoveCartAction;
use Alpha\Controller\Actions\Cart\CalculateVisitorCartAction;
use Alpha\Controller\Actions\Cart\SyncCartAction;
use Alpha\Controller\Actions\Cart\SubmitCheckoutAction;
use Alpha\Controller\Actions\Cart\ShowSuccessAction;
use Alpha\Controller\Actions\Location\GetZonesAction;
use Alpha\Controller\Actions\RedirectToDefaultLanguageAction;



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
$mapperFactory = $registry->get('alpha_mapper_factory');
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
// 4. BIND TWIG NO CONTAINER DE DEPENDÊNCIAS
// ─────────────────────────────────────────────────────────
$container->bind(Environment::class, $twig);


// ─────────────────────────────────────────────────────────
// 5. SLIM APP
// ─────────────────────────────────────────────────────────
AppFactory::setContainer($container);
$app = AppFactory::create();

// Adiciona o Middleware de Idioma para processar a variável {lang} após o roteador
$app->add(new LanguageMiddleware($languageRepository, $twig, $registry));

$app->addRoutingMiddleware();

// Adiciona o Middleware de Redirecionamento de Rotas Legadas (executa primeiro)
$app->add(new LegacyRouteRedirectMiddleware($seoUrlRepository));

// ─────────────────────────────────────────────────────────
// 6. REDIRECIONAMENTOS DE COMPATIBILIDADE / FALLBACKS DE IDIOMA
// ─────────────────────────────────────────────────────────
// Redirecionamentos para o idioma padrão
$app->get('/', RedirectToDefaultLanguageAction::class);
$app->get('/login', RedirectToDefaultLanguageAction::class);
$app->get('/cadastro', RedirectToDefaultLanguageAction::class);
$app->get('/logout', RedirectToDefaultLanguageAction::class);
$app->get('/carrinho', RedirectToDefaultLanguageAction::class);
$app->get('/busca', RedirectToDefaultLanguageAction::class);

$app->map(['GET', 'POST'], '/checkout', RedirectToDefaultLanguageAction::class);

$app->group('/account', function ($account) {
    $account->get('', RedirectToDefaultLanguageAction::class);
    $account->get('/orders', RedirectToDefaultLanguageAction::class);
    $account->get('/order/history/{order_id}', RedirectToDefaultLanguageAction::class);
});


// API para calcular dados do carrinho do visitante (localStorage)
$app->post('/api/carrinho/dados', CalculateVisitorCartAction::class);

// API para sincronizar o carrinho local do visitante com o banco de dados após login
$app->post('/api/carrinho/sincronizar', SyncCartAction::class);

// API para buscar estados (zones) de um país específico
$app->get('/api/paises/{country_id:[0-9]+}/estados', GetZonesAction::class);

// ─────────────────────────────────────────────────────────


// 7. GRUPO DE ROTAS INTERNACIONALIZADAS
// ─────────────────────────────────────────────────────────
$app->group('/{lang:pt-br|en|es}', function (\Slim\Routing\RouteCollectorProxy $group) {

    // Página Inicial do Idioma
    $group->get('', HomeAction::class);

    // Login
    $group->get('/login', ShowLoginFormAction::class);
    $group->post('/login', LoginAction::class);

    // Cadastro
    $group->get('/cadastro', ShowRegistrationFormAction::class);
    $group->post('/cadastro', RegisterAction::class);

    // Logout
    $group->get('/logout', LogoutAction::class);

    // Grupo Protegido: Agrupa o prefixo '/account' E aplica o middleware uma única vez
    $group->group('/account', function ($account) {
        $account->get('', AccountAction::class);
        $account->get('/orders', OrdersAction::class);
        $account->get('/order/history/{order_id}', OrderHistoryAction::class);
    })->add(new \Alpha\Auth\Middleware\SessionMiddleware());

    // Detalhe do Produto (SEO)
    $group->get('/produto/{slug}', ShowProductAction::class);

    // Listagem da Categoria (SEO)
    $group->get('/categoria/{slug}', ShowCategoryAction::class);

    // Página Institucional (SEO)
    $group->get('/pagina/{slug}', ShowInformationAction::class);

    // Mapa do Site (Sitemap) - Rotas corretas e fallbacks/legadas
    $group->get('/mapa-do-site', ShowSitemapAction::class);
    $group->get('/sitemap', ShowSitemapAction::class);
    $group->get('/informacao/sitemap', ShowSitemapAction::class);
    $group->get('/information/sitemap', ShowSitemapAction::class);
    $group->get('/infomation/sitemap', ShowSitemapAction::class);

    // Busca de Produtos
    $group->get('/busca', SearchProductsAction::class);

    // Carrinho de Compras (Exibição e Ações no Banco de Dados)
    $group->get('/carrinho', ShowCartAction::class);
    $group->post('/carrinho/adicionar', AddCartAction::class);
    $group->post('/carrinho/editar', EditCartAction::class);
    $group->get('/carrinho/remover/{key}', RemoveCartAction::class);

    // Checkout
    $group->get('/checkout', \Alpha\Controller\Actions\Cart\Checkout::class);
    $group->post('/checkout', SubmitCheckoutAction::class);

    $group->get('/checkout/sucesso', ShowSuccessAction::class);
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
