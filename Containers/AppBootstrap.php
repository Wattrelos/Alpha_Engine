<?php

namespace Containers;

use Alpha\Mappers\MapperFactory;
use Alpha\Support\Presenters\ImagePresenter;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\UserRepository;
use Alpha\Model\Domain\Repositories\SessionRepository;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Alpha\Model\Domain\Repositories\InformationRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\Repositories\SitemapRepository;
use Alpha\Model\Domain\Repositories\OrderReturnRepository;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;
use Alpha\Session\AlphaSessionHandler;
use Alpha\Auth\Services\CustomerAuthService;
use Alpha\Auth\Services\AdminAuthService;

/**
 * AppBootstrap - Orquestrador de serviços e inicialização de dependências.
 * 
 * Remove a complexidade e código legado de configuração do index.php, 
 * isolando a lógica de bootstrapping da Alpha Engine.
 */
class AppBootstrap
{
    private AppContainer $container;
    private array $configSettings = [];
    private string $languageCode = 'pt-br';
    private int $languageId = 2;
    private ?object $language = null;

    private CategoryRepository $categoryRepository;
    private SeoUrlRepository $seoUrlRepository;
    private LanguageRepository $languageRepository;
    private InformationRepository $informationRepository;

    public function __construct()
    {
        $this->container = new AppContainer();
        $this->initializeServices();
    }

    public static function boot(): self
    {
        return new self();
    }

    private function initializeServices(): void
    {
        $mapperFactory = new MapperFactory($this->container);
        $repositoryFactory = new RepositoryFactory($mapperFactory, $this->container);

        $this->container->bind('alpha_mapper_factory', $mapperFactory);
        $this->container->bind('alpha_repository_factory', $repositoryFactory);

        $settingRepository = new SettingRepository($mapperFactory, $this->container);
        $this->languageRepository = new LanguageRepository($mapperFactory, $this->container);
        $customerRepository = new CustomerRepository($mapperFactory, $this->container);
        $userRepository = new UserRepository($mapperFactory, $this->container);
        $customerAuthService = new CustomerAuthService($customerRepository);
        $adminAuthService = new AdminAuthService($userRepository);

        $sessionRepository = new SessionRepository($mapperFactory, $this->container);
        $sessionHandler = new AlphaSessionHandler($sessionRepository);
        session_set_save_handler($sessionHandler, true);

        // Configurações e Idioma
        $this->configSettings = $settingRepository->getSetting('config', 1);
        $this->languageCode = $this->configSettings['config_language_catalog'] ?? 'pt-br';

        $this->language = $this->languageRepository->getByCode($this->languageCode);
        if (!$this->language) {
            $this->language = $this->languageRepository->find(2); // Fallback pt-br
        }
        $this->languageId = $this->language ? $this->language->getId() : 2;

        // Disponibiliza as configurações essenciais direto no Container PSR-11
        $this->container->bind('configSettings', $this->configSettings);
        $this->container->bind('languageCode', $this->languageCode);
        $this->container->bind('languageId', $this->languageId);

        // Wrapper para retrocompatibilidade do objeto config
        $configWrapper = new class($this->configSettings, $this->languageId) {
            private array $settings;
            private int $languageId;
            public function __construct(array $settings, int $languageId)
            {
                $this->settings = $settings;
                $this->languageId = $languageId;
            }
            public function get(string $key)
            {
                if ($key === 'config_language_id') {
                    return $this->languageId;
                }
                return $this->settings[$key] ?? null;
            }
            public function set(string $key, $value): void
            {
                $this->settings[$key] = $value;
            }
        };
        $this->container->bind('config', $configWrapper);

        // Wrapper para retrocompatibilidade do construtor de URLs
        $urlWrapper = new class($this->languageCode, $this->configSettings) {
            private string $lang;
            private string $server;
            public function __construct(string $lang, array $settings)
            {
                $this->lang = $lang;
                $this->server = $settings['config_url'] ?? HTTP_SERVER;
            }
            public function link(string $route, string $args = '', bool $secure = true): string
            {
                $route = trim($route, '/');
                $routeMap = [
                    'common/home'             => '',
                    'account'                 => 'account',
                    'account/account'         => 'account',
                    'account/edit'            => 'account/edit',
                    'account/password'        => 'account/resetar-senha',
                    'account/address'         => 'account/addresses',
                    'account/address/add'     => 'account/address/create',
                    'account/address/edit'    => 'account/address/edit',
                    'account/address/delete'  => 'account/address/delete',
                    'account/orders'          => 'account/orders',
                    'account/newsletter'      => 'account/newsletter',
                    'account/wishlist'        => 'account/wishlist',
                    'account/return'          => 'account/return',
                    'account/return/add'      => 'account/return/add',
                    'account/transaction'     => 'account/transaction',
                    'checkout/cart'           => 'carrinho',
                    'checkout/checkout'       => 'checkout',
                    'product/search'          => 'busca',
                    'information/contact'     => 'contato',
                    'information/sitemap'     => 'sitemap',
                    'product/special'         => 'busca',
                    'product/manufacturer'    => 'busca',
                    'account/affiliate'       => 'account',
                ];

                $friendlyRoute = $routeMap[$route] ?? $route;

                if ($route === 'product/category' && !empty($args)) {
                    parse_str($args, $parsedArgs);
                    if (isset($parsedArgs['path'])) {
                        $friendlyRoute = 'categoria/' . $parsedArgs['path'];
                    }
                }

                if ($route === 'information/information' && !empty($args)) {
                    parse_str($args, $parsedArgs);
                    if (isset($parsedArgs['information_id'])) {
                        $friendlyRoute = 'pagina/' . $parsedArgs['information_id'];
                    }
                }

                return $this->server . $this->lang . '/' . $friendlyRoute;
            }
        };
        $this->container->bind('url', $urlWrapper);

        // Alpha Engine: Inicializa o Tradutor Support\Language e carrega o idioma principal
        $translator = new \Alpha\Support\Language($this->languageCode);
        $translator->load($this->languageCode);

        // Vincula o tradutor nativo ao container
        $this->container->bind('language', $translator);
        $this->container->bind(\Alpha\Support\Language::class, $translator);
        $this->container->bind('languageEntity', $this->language);

        $sessionMock = new \stdClass();
        $sessionMock->data = []; // Evita erros de "property of non-object" no legado
        $this->container->bind('session', $sessionMock);

        // Alpha Engine: Instancia e vincula o helper de dados do cliente
        $customerHelper = new \Alpha\Support\Customer();
        $this->container->bind('customer', $customerHelper);
        $this->container->bind(\Alpha\Support\Customer::class, $customerHelper);

        // Alpha Engine: Disponibiliza o ImagePresenter para injeção via Container
        $configUrl  = $this->configSettings['config_url'] ?? HTTP_SERVER;
        $imageDir   = defined('DIR_IMAGE') ? DIR_IMAGE : (DIR_ROOT . 'image/');
        $imagePresenter = new ImagePresenter($configUrl, $imageDir);
        $this->container->bind(ImagePresenter::class, $imagePresenter);

        // Alpha Engine: Instancia e vincula helpers de Moeda e Imposto
        $currencyHelper = new \Alpha\Support\Currency($translator);
        $this->container->bind('currency', $currencyHelper);
        $this->container->bind(\Alpha\Support\Currency::class, $currencyHelper);

        $taxHelper = new \Alpha\Support\Tax($this->container);
        $this->container->bind('tax', $taxHelper);
        $this->container->bind(\Alpha\Support\Tax::class, $taxHelper);

        // Alpha Engine: Inicialização do EventDispatcher e Ouvintes (RabbitMQ)
        $queueService = new \Alpha\Events\QueueService();
        $orderCreatedListener = new \Alpha\Events\OrderCreatedListener($queueService);
        $eventDispatcher = new \Alpha\Events\EventDispatcher();
        $eventDispatcher->addListener('order.created', $orderCreatedListener);

        $this->container->bind(\Alpha\Events\EventDispatcher::class, $eventDispatcher);

        // Repositórios de Domínio
        $this->categoryRepository = new CategoryRepository($mapperFactory, $this->container);
        $productRepository = $repositoryFactory->get(ProductRepository::class);
        $this->seoUrlRepository = $repositoryFactory->get(SeoUrlRepository::class);
        $this->informationRepository = $repositoryFactory->get(InformationRepository::class);
        $orderRepository = $repositoryFactory->get(OrderRepository::class);
        $cartRepository = $repositoryFactory->get(CartRepository::class);
        $sitemapRepository = $repositoryFactory->get(SitemapRepository::class);
        $manufacturerRepository = $repositoryFactory->get(ManufacturerRepository::class);
        $customerAddressesRepository = $repositoryFactory->get(CustomerAddressesRepository::class);
        $orderReturnRepository = $repositoryFactory->get(OrderReturnRepository::class);

        // Bindings no Container de Dependências
        $this->container
            ->bind(CategoryRepository::class, $this->categoryRepository)
            ->bind(SettingRepository::class, $settingRepository)
            ->bind(LanguageRepository::class, $this->languageRepository)
            ->bind(CustomerRepository::class, $customerRepository)
            ->bind(UserRepository::class, $userRepository)
            ->bind(CustomerAuthService::class, $customerAuthService)
            ->bind(AdminAuthService::class, $adminAuthService)
            ->bind(SessionRepository::class, $sessionRepository)
            ->bind(ProductRepository::class, $productRepository)
            ->bind(SeoUrlRepository::class, $this->seoUrlRepository)
            ->bind(InformationRepository::class, $this->informationRepository)
            ->bind(OrderRepository::class, $orderRepository)
            ->bind(CartRepository::class, $cartRepository)
            ->bind(SitemapRepository::class, $sitemapRepository)
            ->bind(ManufacturerRepository::class, $manufacturerRepository)
            ->bind(CustomerAddressesRepository::class, $customerAddressesRepository)
            ->bind(OrderReturnRepository::class, $orderReturnRepository)
            ->bind(MapperFactory::class, $mapperFactory)
            ->bind(RepositoryFactory::class, $repositoryFactory);
    }

    public function getContainer(): AppContainer
    {
        return $this->container;
    }

    /**
     * Alias de retrocompatibilidade para componentes que esperam um "Registry".
     * Como o AppContainer implementa PSR-11 (get/has), ele atua perfeitamente no lugar do Registry legado.
     */
    public function getRegistry(): AppContainer
    {
        return $this->container;
    }

    public function getConfigSettings(): array
    {
        return $this->configSettings;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function getLanguage(): ?object
    {
        return $this->language;
    }

    public function getCategoryRepository(): CategoryRepository
    {
        return $this->categoryRepository;
    }

    public function getSeoUrlRepository(): SeoUrlRepository
    {
        return $this->seoUrlRepository;
    }

    public function getLanguageRepository(): LanguageRepository
    {
        return $this->languageRepository;
    }

    public function getInformationRepository(): InformationRepository
    {
        return $this->informationRepository;
    }
}
