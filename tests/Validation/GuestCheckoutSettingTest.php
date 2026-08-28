<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../backend/config.php';

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Alpha\Admin\Controllers\Actions\Setting\StoreSetting\UpdateStoreSettingAction;
use Alpha\Controller\Actions\Cart\SubmitCheckoutAction;
use Alpha\Controller\Actions\Cart\Checkout;
use Alpha\Support\StoreSettings;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Containers\AppBootstrap;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;
use Slim\Factory\AppFactory;

#[CoversClass(UpdateStoreSettingAction::class)]
#[CoversClass(SubmitCheckoutAction::class)]
#[CoversClass(StoreSettings::class)]
class GuestCheckoutSettingTest extends TestCase
{
    private $container;
    private ServerRequestFactory $requestFactory;
    private StreamFactory $streamFactory;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $bootstrap = AppBootstrap::boot();
        $this->container = $bootstrap->getContainer();
        $this->requestFactory = new ServerRequestFactory();
        $this->streamFactory = new StreamFactory();
    }

    protected function tearDown(): void
    {
        /** @var SettingRepository $settingRepo */
        $settingRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(SettingRepository::class);
        $currentSettings = $settingRepo->getSetting('config', 1);
        $settingRepo->editSetting('config', array_merge($currentSettings, ['config_checkout_guest' => '1']), 1);
        unset($_SESSION['customer_id'], $_SESSION['customer_group_id']);
    }

    public function testStoreSettingsHelperTransformsCheckoutGuest(): void
    {
        $rawSettingsWithGuestEnabled = ['config_name' => 'Teste Loja', 'config_checkout_guest' => '1'];
        $helper1 = new StoreSettings($rawSettingsWithGuestEnabled, 2);
        $formatted1 = $helper1->getFormattedSettings(false);
        $this->assertTrue($formatted1['checkoutGuest']);

        $rawSettingsWithGuestDisabled = ['config_name' => 'Teste Loja', 'config_checkout_guest' => '0'];
        $helper2 = new StoreSettings($rawSettingsWithGuestDisabled, 2);
        $formatted2 = $helper2->getFormattedSettings(false);
        $this->assertFalse($formatted2['checkoutGuest']);
    }

    public function testSubmitCheckoutBlocksGuestWhenCheckoutGuestIsDisabled(): void
    {
        // Força a sessão a não ter cliente logado
        $_SESSION['customer_id'] = 0;
        unset($_SESSION['customer_id']);

        // Configura container com config_checkout_guest = '0'
        $customSettings = [
            'config_store_id' => 1,
            'config_language_id' => 2,
            'config_currency_id' => 1,
            'config_country_id' => 76,
            'config_customer_group_id' => 1,
            'config_checkout_guest' => '0',
        ];
        $this->container->bind('configSettings', $customSettings);

        $eventDispatcher = $this->container->get(\Alpha\Events\EventDispatcher::class);
        $action = new SubmitCheckoutAction($this->container, $eventDispatcher);

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/checkout')
            ->withHeader('Accept', 'application/json')
            ->withParsedBody([
                'payment_firstname' => 'Visitante',
                'payment_lastname'  => 'Anônimo',
                'email'             => 'visitante@teste.com',
                'payment_street'    => 'Rua Teste',
                'payment_number'    => '123',
                'payment_city'      => 'São Paulo',
                'payment_postcode'  => '01001-000',
                'payment_zone_id'   => 'SP',
                'payment_method'    => 'cod',
            ]);

        $response = new Response();
        $res = $action($request, $response, []);

        $this->assertEquals(403, $res->getStatusCode());
        $body = json_decode((string)$res->getBody(), true);
        $this->assertEquals('GUEST_CHECKOUT_DISABLED', $body['error']);
    }

    public function testSubmitCheckoutAllowsAuthenticatedCustomerWhenCheckoutGuestIsDisabled(): void
    {
        // Simula cliente logado
        $_SESSION['customer_id'] = 999;
        $_SESSION['customer_group_id'] = 1;

        $customSettings = [
            'config_store_id' => 1,
            'config_language_id' => 2,
            'config_currency_id' => 1,
            'config_country_id' => 76,
            'config_customer_group_id' => 1,
            'config_checkout_guest' => '0',
        ];
        $this->container->bind('configSettings', $customSettings);

        $eventDispatcher = $this->container->get(\Alpha\Events\EventDispatcher::class);
        $action = new SubmitCheckoutAction($this->container, $eventDispatcher);

        $request = $this->requestFactory->createServerRequest('POST', '/pt-br/checkout')
            ->withHeader('Accept', 'application/json')
            ->withParsedBody([
                'payment_firstname' => 'Cliente',
                'payment_lastname'  => 'Cadastrado',
                'email'             => 'cliente@teste.com',
                'payment_street'    => 'Rua Teste',
                'payment_number'    => '123',
                'payment_city'      => 'São Paulo',
                'payment_postcode'  => '01001-000',
                'payment_zone_id'   => 'SP',
                'payment_method'    => 'cod',
            ]);

        $response = new Response();
        $res = $action($request, $response, []);

        // O status não deve ser 403 (GUEST_CHECKOUT_DISABLED)
        $this->assertNotEquals(403, $res->getStatusCode());

        // Limpa a sessão
        unset($_SESSION['customer_id']);
    }

    public function testUpdateStoreSettingActionSavesCheckoutGuestOption(): void
    {
        /** @var SettingRepository $settingRepo */
        $settingRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(SettingRepository::class);
        $originalSettings = $settingRepo->getSetting('config', 1);

        $action = new UpdateStoreSettingAction($this->container);

        $request = $this->requestFactory->createServerRequest('POST', '/LPDHED2dC7Gjrg2b/configuracoes')
            ->withParsedBody([
                'config_name'           => 'Minha Loja Alpha',
                'config_owner'          => 'Administrador',
                'config_email'          => 'admin@sualoja.com',
                'config_telephone'      => '(11) 98765-4321',
                'config_address'        => 'Av. Paulista, 1000 - Bela Vista',
                'meta_title'            => 'Minha Loja Alpha',
                'meta_description'      => 'Loja Virtual',
                'meta_keyword'          => 'loja, virtual',
                'config_checkout_guest' => '0',
            ]);

        $response = new Response();
        $res = $action($request, $response, []);

        $this->assertEquals(302, $res->getStatusCode());

        // Verifica se a chave foi salva no banco
        $updatedSettings = $settingRepo->getSetting('config', 1);
        $this->assertEquals('0', $updatedSettings['config_checkout_guest']);

        // Restaura a configuração original
        if (isset($originalSettings['config_checkout_guest'])) {
            $settingRepo->editSetting('config', array_merge($updatedSettings, ['config_checkout_guest' => $originalSettings['config_checkout_guest']]), 1);
        } else {
            $settingRepo->editSetting('config', array_merge($updatedSettings, ['config_checkout_guest' => '1']), 1);
        }
    }

    public function testEditStoreSettingTwigTemplateRendersOptionsTabProperly(): void
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../backend/resources/views');
        $twig = new \Twig\Environment($loader, ['cache' => false]);

        $rendered = $twig->render('admin/setting/store_setting/edit.html.twig', [
            'admin_path' => '/admin',
            'settings' => ['config_name' => 'Minha Loja', 'config_checkout_guest' => '1'],
            'AdminLang' => [
                'tab_options' => 'Opções de Compra',
                'title_options' => 'Opções de Compra e Finalização de Pedidos',
                'entry_checkout_guest' => 'Permitir Compras como Visitante (Guest Checkout)'
            ],
            'languages' => [],
            'information_pages' => [],
            'errors' => []
        ]);

        // Valida que o botão da aba e o container de conteúdo existem no HTML
        $this->assertStringContainsString('id="tab-btn-options"', $rendered);
        $this->assertStringContainsString('id="tab-content-options"', $rendered);
        $this->assertStringContainsString('name="config_checkout_guest"', $rendered);
        $this->assertStringContainsString('control-checkout-guest', $rendered);

        // Valida que a aba Opções de Compra NÃO está aninhada dentro de tab-content-information
        $posInformation = strpos($rendered, 'id="tab-content-information"');
        $posOptions = strpos($rendered, 'id="tab-content-options"');
        $this->assertLessThan($posInformation, $posOptions, 'tab-content-options deve ser renderizada antes de tab-content-information como um elemento irmão independente.');
    }
}
