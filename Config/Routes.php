<?php

use Slim\Routing\RouteCollectorProxy;
use Alpha\Auth\Middleware\SessionMiddleware;

// Importe suas Actions aqui...
use Alpha\Controller\Actions\Main\HomeAction;
use Alpha\Controller\Actions\Customer\Auth\ShowLoginFormAction;
use Alpha\Controller\Actions\Customer\Auth\LoginAction;
use Alpha\Controller\Actions\Customer\Auth\ShowRegistrationFormAction;
use Alpha\Controller\Actions\Customer\Auth\RegisterAction;
use Alpha\Controller\Actions\Customer\Auth\LogoutAction;
use Alpha\Controller\Actions\Customer\Auth\AccountAction;
use Alpha\Controller\Actions\Customer\OrdersAction;
use Alpha\Controller\Actions\Customer\OrderHistoryAction;
use Alpha\Controller\Actions\Product\ShowProductAction;
use Alpha\Controller\Actions\Category\ShowCategoryAction;
use Alpha\Controller\Actions\Information\ShowInformationAction;
use Alpha\Controller\Actions\Information\ShowSitemapAction;
use Alpha\Controller\Actions\Product\SearchProductsAction;
use Alpha\Controller\Actions\Cart\ShowCartAction;
use Alpha\Controller\Actions\Cart\AddCartAction;
use Alpha\Controller\Actions\Cart\EditCartAction;
use Alpha\Controller\Actions\Cart\RemoveCartAction;
use Alpha\Controller\Actions\Cart\SubmitCheckoutAction;
use Alpha\Controller\Actions\Cart\ShowSuccessAction;
use Alpha\Controller\Actions\RedirectToDefaultLanguageAction;
use Alpha\Controller\Actions\Cart\CalculateVisitorCartAction;
use Alpha\Controller\Actions\Cart\SyncCartAction;
use Alpha\Controller\Actions\Location\GetZonesAction;

return function (\Slim\App $app) {

    // ─────────────────────────────────────────────────────────
    // 1. REDIRECIONAMENTOS DE COMPATIBILIDADE / FALLBACKS DE IDIOMA
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

    // ─────────────────────────────────────────────────────────
    // 2. APIs INTERNAS DA APLICAÇÃO
    // ─────────────────────────────────────────────────────────
    // API para calcular dados do carrinho do visitante (localStorage)
    $app->post('/api/carrinho/dados', CalculateVisitorCartAction::class);

    // API para sincronizar o carrinho local do visitante com o banco de dados após login
    $app->post('/api/carrinho/sincronizar', SyncCartAction::class);

    // API para buscar estados (zones) de um país específico
    $app->get('/api/paises/{country_id:[0-9]+}/estados', GetZonesAction::class);

    // ─────────────────────────────────────────────────────────
    // 3. GRUPO DE ROTAS INTERNACIONALIZADAS
    // ─────────────────────────────────────────────────────────
    $app->group('/{lang:pt-br|en|es}', function (RouteCollectorProxy $group) {

        // Página Inicial do Idioma
        $group->get('', HomeAction::class)->setName('home');

        // Login
        $group->get('/login', ShowLoginFormAction::class)->setName('login.form');
        $group->post('/login', LoginAction::class)->setName('login.submit');

        // Cadastro
        $group->get('/cadastro', ShowRegistrationFormAction::class)->setName('register.form');
        $group->post('/cadastro', RegisterAction::class)->setName('register.submit');

        // Logout
        $group->get('/logout', LogoutAction::class)->setName('logout');

        // Grupo Protegido
        $group->group('/account', function (RouteCollectorProxy $account) {
            $account->get('', AccountAction::class)->setName('account.index');
            $account->get('/orders', OrdersAction::class)->setName('account.orders');
            $account->get('/order/history/{order_id}', OrderHistoryAction::class)->setName('account.order.history');
        })->add(new SessionMiddleware());

        // Detalhe do Produto, Categoria e Institucional (SEO)
        $group->get('/produto/{slug}', ShowProductAction::class)->setName('product.detail');
        $group->get('/categoria/{slug}', ShowCategoryAction::class)->setName('category.detail');
        $group->get('/pagina/{slug}', ShowInformationAction::class)->setName('info.page');

        // Mapa do Site (Sitemap)
        $group->get('/mapa-do-site', ShowSitemapAction::class)->setName('sitemap');
        $group->get('/sitemap', ShowSitemapAction::class);
        $group->get('/informacao/sitemap', ShowSitemapAction::class);
        $group->get('/information/sitemap', ShowSitemapAction::class);
        $group->get('/infomation/sitemap', ShowSitemapAction::class);

        // Busca de Produtos
        $group->get('/busca', SearchProductsAction::class)->setName('search');

        // Carrinho de Compras
        $group->get('/carrinho', ShowCartAction::class)->setName('cart.index');
        $group->post('/carrinho/adicionar', AddCartAction::class)->setName('cart.add');
        $group->post('/carrinho/editar', EditCartAction::class)->setName('cart.edit');
        $group->get('/carrinho/remover/{key}', RemoveCartAction::class)->setName('cart.remove');

        // Checkout
        $group->get('/checkout', \Alpha\Controller\Actions\Cart\Checkout::class)->setName('checkout.index');
        $group->post('/checkout', SubmitCheckoutAction::class)->setName('checkout.submit');
        $group->get('/checkout/sucesso', ShowSuccessAction::class)->setName('checkout.success');
    });
};

