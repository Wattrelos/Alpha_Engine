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
use Alpha\Controller\Actions\Customer\Auth\RequestPasswordResetAction;
use Alpha\Controller\Actions\Customer\Auth\ResetPasswordAction;
use Alpha\Controller\Actions\Customer\OrdersAction;
use Alpha\Controller\Actions\Customer\OrderHistoryAction;
use Alpha\Controller\Actions\Customer\Addresses\ShowAddressesAction;
use Alpha\Controller\Actions\Customer\Addresses\CreateAddressAction;
use Alpha\Controller\Actions\Customer\Addresses\EditAddressAction;
use Alpha\Controller\Actions\Customer\Addresses\DeleteAddressAction;
use Alpha\Controller\Actions\Product\ShowProductAction;
use Alpha\Controller\Actions\Category\ShowCategoryAction;
use Alpha\Controller\Actions\Information\ShowInformationAction;
use Alpha\Controller\Actions\Information\ShowSitemapAction;
use Alpha\Controller\Actions\Information\ShowContactAction;
use Alpha\Controller\Actions\Product\ProductReturnsAction;
use Alpha\Controller\Actions\Product\SearchAction;
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
    // ROTAS DO PAINEL ADMINISTRATIVO (ADMIN)
    // ─────────────────────────────────────────────────────────
    if (defined('APPLICATION') && APPLICATION === 'admin') {
        $app->get('/', \Alpha\Admin\Controllers\Actions\Auth\ShowLoginAction::class)->setName('admin.login.form');
        $app->post('/login', \Alpha\Admin\Controllers\Actions\Auth\LoginAction::class)->setName('admin.login.submit');
        $app->get('/setup', \Alpha\Admin\Controllers\Actions\Auth\ShowSetupAction::class)->setName('admin.setup.form');
        $app->post('/setup', \Alpha\Admin\Controllers\Actions\Auth\SetupAction::class)->setName('admin.setup.submit');
        
        // Grupo de rotas protegidas do painel administrativo
        $app->group('', function (RouteCollectorProxy $group) {
            $group->get('/dashboard', \Alpha\Admin\Controllers\Actions\Dashboard\ViewDashboardAction::class)->setName('admin.dashboard');
            $group->get('/produtos', \Alpha\Admin\Controllers\Actions\Catalog\Product\ListProductsAction::class)->setName('admin.product.list');
            $group->get('/produtos/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Catalog\Product\EditProductAction::class)->setName('admin.product.edit');
            $group->post('/produtos/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Catalog\Product\UpdateProductAction::class)->setName('admin.product.update');
            
            // Categorias
            $group->get('/categorias', \Alpha\Admin\Controllers\Actions\Catalog\Category\ListCategoriesAction::class)->setName('admin.category.list');
            $group->map(['GET', 'POST'], '/categorias/criar', \Alpha\Admin\Controllers\Actions\Catalog\Category\CreateCategoryAction::class)->setName('admin.category.create');
            $group->get('/categorias/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Catalog\Category\EditCategoryAction::class)->setName('admin.category.edit');
            $group->post('/categorias/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Catalog\Category\UpdateCategoryAction::class)->setName('admin.category.update');
            $group->get('/categorias/{id:[0-9]+}/excluir', \Alpha\Admin\Controllers\Actions\Catalog\Category\DeleteCategoryAction::class)->setName('admin.category.delete');

            // Fabricantes
            $group->get('/fabricantes', \Alpha\Admin\Controllers\Actions\Catalog\Manufacturer\ListManufacturersAction::class)->setName('admin.manufacturer.list');
            $group->get('/fabricantes/criar', \Alpha\Admin\Controllers\Actions\Catalog\Manufacturer\CreateManufacturerAction::class)->setName('admin.manufacturer.create');
            $group->post('/fabricantes/criar', \Alpha\Admin\Controllers\Actions\Catalog\Manufacturer\StoreManufacturerAction::class)->setName('admin.manufacturer.store');
            $group->get('/fabricantes/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Catalog\Manufacturer\EditManufacturerAction::class)->setName('admin.manufacturer.edit');
            $group->post('/fabricantes/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Catalog\Manufacturer\UpdateManufacturerAction::class)->setName('admin.manufacturer.update');
            $group->get('/fabricantes/{id:[0-9]+}/excluir', \Alpha\Admin\Controllers\Actions\Catalog\Manufacturer\DeleteManufacturerAction::class)->setName('admin.manufacturer.delete');

            // Fornecedores
            $group->get('/fornecedores', \Alpha\Admin\Controllers\Actions\Procurement\Supplier\ListSuppliersAction::class)->setName('admin.supplier.list');
            $group->get('/fornecedores/criar', \Alpha\Admin\Controllers\Actions\Procurement\Supplier\CreateSupplierAction::class)->setName('admin.supplier.create');
            $group->post('/fornecedores/criar', \Alpha\Admin\Controllers\Actions\Procurement\Supplier\StoreSupplierAction::class)->setName('admin.supplier.store');
            $group->get('/fornecedores/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Procurement\Supplier\EditSupplierAction::class)->setName('admin.supplier.edit');
            $group->post('/fornecedores/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Procurement\Supplier\UpdateSupplierAction::class)->setName('admin.supplier.update');
            $group->get('/fornecedores/{id:[0-9]+}/excluir', \Alpha\Admin\Controllers\Actions\Procurement\Supplier\DeleteSupplierAction::class)->setName('admin.supplier.delete');

            // Clientes
            $group->get('/clientes', \Alpha\Admin\Controllers\Actions\Customer\Customer\ListCustomersAction::class)->setName('admin.customer.list');
            $group->map(['GET', 'POST'], '/clientes/criar', \Alpha\Admin\Controllers\Actions\Customer\Customer\CreateCustomerAction::class)->setName('admin.customer.create');
            $group->map(['GET', 'POST'], '/clientes/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Customer\Customer\EditCustomerAction::class)->setName('admin.customer.edit');
            $group->get('/clientes/{id:[0-9]+}', \Alpha\Admin\Controllers\Actions\Customer\Customer\ShowCustomerAction::class)->setName('admin.customer.show');

            // Endereços de Clientes
            $group->map(['GET', 'POST'], '/clientes/{customer_id:[0-9]+}/enderecos/criar', \Alpha\Admin\Controllers\Actions\Customer\Address\CreateAddressAction::class)->setName('admin.customer.address.create');
            $group->map(['GET', 'POST'], '/clientes/{customer_id:[0-9]+}/enderecos/{id:[0-9]+}/editar', \Alpha\Admin\Controllers\Actions\Customer\Address\EditAddressAction::class)->setName('admin.customer.address.edit');
            $group->get('/clientes/{customer_id:[0-9]+}/enderecos/{id:[0-9]+}/excluir', \Alpha\Admin\Controllers\Actions\Customer\Address\DeleteAddressAction::class)->setName('admin.customer.address.delete');

            // Configurações da Loja
            $group->get('/configuracoes', \Alpha\Admin\Controllers\Actions\Setting\StoreSetting\EditStoreSettingAction::class)->setName('admin.setting.edit');
            $group->post('/configuracoes', \Alpha\Admin\Controllers\Actions\Setting\StoreSetting\UpdateStoreSettingAction::class)->setName('admin.setting.update');

            // Gestão de Pedidos (Vendas)
            $group->get('/pedidos', \Alpha\Admin\Controllers\Actions\Sales\Order\ListOrdersAction::class)->setName('admin.orders.index');
            $group->get('/pedidos/{id:[0-9]+}', \Alpha\Admin\Controllers\Actions\Sales\Order\ShowOrderAction::class)->setName('admin.orders.show');
            $group->get('/pedidos/{id:[0-9]+}/fatura', \Alpha\Admin\Controllers\Actions\Sales\Order\ViewOrderDetailsAction::class)->setName('admin.orders.invoice');
            $group->post('/pedidos/{id:[0-9]+}/status', \Alpha\Admin\Controllers\Actions\Sales\Order\UpdateOrderStatusAction::class)->setName('admin.orders.update_status');

            $group->get('/logout', \Alpha\Admin\Controllers\Actions\Auth\LogoutAction::class)->setName('admin.logout');
        })->add(new \Alpha\Auth\Middleware\AdminSessionMiddleware($app->getContainer()));
        
        return;
    }

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
    $app->map(['GET', 'POST'], '/contato', RedirectToDefaultLanguageAction::class);
    $app->map(['GET', 'POST'], '/contact', RedirectToDefaultLanguageAction::class);
    $app->map(['GET', 'POST'], '/recuperar-senha', RedirectToDefaultLanguageAction::class);
    $app->map(['GET', 'POST'], '/resetar-senha', RedirectToDefaultLanguageAction::class);

    $app->map(['GET', 'POST'], '/checkout', RedirectToDefaultLanguageAction::class);

    $app->group('/account', function ($account) {
        $account->get('', RedirectToDefaultLanguageAction::class);
        $account->get('/orders', RedirectToDefaultLanguageAction::class);
        $account->get('/order/history/{order_id}', RedirectToDefaultLanguageAction::class);
        $account->get('/addresses', RedirectToDefaultLanguageAction::class);
        $account->get('/address/create', RedirectToDefaultLanguageAction::class);
        $account->get('/address/{address_id:[0-9]+}/edit', RedirectToDefaultLanguageAction::class);
        $account->get('/address/{address_id:[0-9]+}/delete', RedirectToDefaultLanguageAction::class);
        $account->get('/return', RedirectToDefaultLanguageAction::class);
        $account->get('/wishlist', RedirectToDefaultLanguageAction::class);
        $account->get('/edit', RedirectToDefaultLanguageAction::class);
        $account->map(['GET', 'POST'], '/resetar-senha', RedirectToDefaultLanguageAction::class);
        $account->get('/transaction', RedirectToDefaultLanguageAction::class);
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

    // Novas APIs do sistema de endereçamento Geo
    $app->get('/api/geo/paises/{country_id:[0-9]+}/estados', \Alpha\Controller\Actions\Location\GetGeoZonesAction::class);
    $app->get('/api/geo/estados/{zone_id:[0-9]+}/cidades', \Alpha\Controller\Actions\Location\GetGeoCitiesAction::class);

    // API para salvar dados de CEP/ViaCEP consultados
    $app->post('/api/carrinho/salvar-cep', \Alpha\Controller\Actions\Cart\SaveShippingCepAction::class);

    // ─────────────────────────────────────────────────────────
    // 3. GRUPO DE ROTAS INTERNACIONALIZADAS
    // ─────────────────────────────────────────────────────────
    $app->group('/{lang:pt-br|en|es}', function (RouteCollectorProxy $group) {

        // Página Inicial do Idioma
        $group->get('', HomeAction::class)->setName('home');

        // Login
        $group->get('/login', ShowLoginFormAction::class)->setName('login.form');
        $group->post('/login', LoginAction::class)->setName('login.submit');

        // Recuperar e Resetar Senha
        $group->map(['GET', 'POST'], '/recuperar-senha', RequestPasswordResetAction::class)->setName('account.recuperar-senha');
        $group->map(['GET', 'POST'], '/resetar-senha', ResetPasswordAction::class)->setName('account.resetar-senha');

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
            $account->get('/return', ProductReturnsAction::class)->setName('account.returns');

            // ── Editar Conta ─────────────────────────────────────────────────
            $account->get('/edit', \Alpha\Controller\Actions\Customer\Account\UpdateAction::class)->setName('account.edit');
            $account->post('/edit', \Alpha\Controller\Actions\Customer\Account\UpdateAction::class);
            $account->map(['GET', 'POST'], '/resetar-senha', ResetPasswordAction::class)->setName('account.resetar-senha.logged');

            // ── Newsletter ───────────────────────────────────────────────────
            $account->post('/newsletter', \Alpha\Controller\Actions\Customer\Account\NewsletterAction::class)->setName('account.newsletter');

            // ── Transações ───────────────────────────────────────────────────
            $account->get('/transaction', \Alpha\Controller\Actions\Customer\Account\TransactionAction::class)->setName('account.transaction');

            // ── Lista de Desejos (Wishlist) ──────────────────────────────────
            $account->get('/wishlist', \Alpha\Controller\Actions\Customer\Account\WishlistAction::class)->setName('account.wishlist');
            $account->post('/wishlist/add', \Alpha\Controller\Actions\Customer\Account\WishlistAddAction::class)->setName('account.wishlist.add');
            $account->get('/wishlist/remove/{product_id:[0-9]+}', \Alpha\Controller\Actions\Customer\Account\WishlistRemoveAction::class)->setName('account.wishlist.remove');

            // ── Endereços ────────────────────────────────────────────────────
            $account->get('/addresses', ShowAddressesAction::class)->setName('account.addresses');
            $account->get('/address/create', CreateAddressAction::class)->setName('account.address.create');
            $account->post('/address/create', CreateAddressAction::class);
            $account->get('/address/{address_id:[0-9]+}/edit',   EditAddressAction::class)->setName('account.address.edit');
            $account->post('/address/{address_id:[0-9]+}/edit',  EditAddressAction::class);
            $account->get('/address/{address_id:[0-9]+}/delete', DeleteAddressAction::class)->setName('account.address.delete');
        })->add(new SessionMiddleware());

        // Detalhe do Produto, Categoria e Institucional (SEO)
        $group->get('/produto/{slug}',   ShowProductAction::class)->setName('product.detail');
        $group->get('/categoria/{slug}', ShowCategoryAction::class)->setName('category.detail');
        $group->get('/pagina/{slug}',    ShowInformationAction::class)->setName('info.page');

        // Mapa do Site (Sitemap)
        $group->get('/mapa-do-site', ShowSitemapAction::class)->setName('sitemap');
        $group->get('/sitemap', ShowSitemapAction::class);
        $group->get('/informacao/sitemap', ShowSitemapAction::class);
        $group->get('/information/sitemap', ShowSitemapAction::class);
        $group->get('/infomation/sitemap', ShowSitemapAction::class);

        // Contato (Contact)
        $group->map(['GET', 'POST'], '/contato', ShowContactAction::class)->setName('contact');
        $group->map(['GET', 'POST'], '/contact', ShowContactAction::class);
        $group->map(['GET', 'POST'], '/informacao/contato', ShowContactAction::class);
        $group->map(['GET', 'POST'], '/information/contact', ShowContactAction::class);

        // Busca de Produtos
        $group->get('/busca', SearchAction::class)->setName('search');

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
