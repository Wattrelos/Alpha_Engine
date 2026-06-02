<?php

namespace Containers;

use Alpha\Support\Registry;
use Alpha\Mappers\MapperFactory;
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
use Alpha\Model\Domain\Repositories\AddressRepository;
use Alpha\Session\AlphaSessionHandler;
use Alpha\Auth\Services\CustomerAuthService;
use Alpha\Auth\Services\AdminAuthService;
use Alpha\Support\Config;
use Alpha\Support\Language;
use Alpha\Support\Session;
use Alpha\Support\Customer;
use Alpha\Support\Tax;
use Alpha\Support\Currency;
use Alpha\Support\Weight;
use Alpha\Support\Url;
use Alpha\Support\Document;

/**
 * AppBootstrap - Orquestrador de serviços e inicialização de dependências.
 * 
 * Remove a complexidade e código legado de configuração do index.php, 
 * isolando a lógica de bootstrapping da Alpha Engine.
 */
class AppBootstrap
{
    private Registry $registry;
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
        $this->registry = new Registry();
        $this->container = new AppContainer();
        $this->initializeServices();
    }

    public static function boot(): self
    {
        return new self();
    }

    private function initializeServices(): void
    {
        $mapperFactory = new MapperFactory($this->registry);
        $repositoryFactory = new RepositoryFactory($mapperFactory, $this->registry);

        $this->registry->set('alpha_mapper_factory', $mapperFactory);
        $this->registry->set('alpha_repository_factory', $repositoryFactory);

        $settingRepository = new SettingRepository($mapperFactory, $this->registry);
        $this->languageRepository = new LanguageRepository($mapperFactory, $this->registry);
        $customerRepository = new CustomerRepository($mapperFactory, $this->registry);
        $userRepository = new UserRepository($mapperFactory, $this->registry);
        $customerAuthService = new CustomerAuthService($customerRepository);
        $adminAuthService = new AdminAuthService($userRepository);

        $sessionRepository = new SessionRepository($mapperFactory, $this->registry);
        $sessionHandler = new AlphaSessionHandler($sessionRepository);
        session_set_save_handler($sessionHandler, true);

        // Configurações e Idioma
        $this->configSettings = $settingRepository->getSetting('config', 0);
        $this->languageCode = $this->configSettings['config_language_catalog'] ?? 'pt-br';

        $this->language = $this->languageRepository->getByCode($this->languageCode);
        if (!$this->language) {
            $this->language = $this->languageRepository->find(2); // Fallback pt-br
        }
        $this->languageId = $this->language ? $this->language->getId() : 2;

        // Injetando adaptadores no Registry
        $this->registry->set('config', new Config(array_merge([
            'config_customer_group_id' => 1,
            'config_tax' => false,
            'config_customer_price' => false,
            'config_language' => $this->languageCode,
            'config_language_id' => $this->languageId,
        ], $this->configSettings)));

        $languageAdaptor = new Language($this->languageCode);
        $this->registry->set('language', $languageAdaptor);
        $this->registry->set('session', new Session());
        $this->registry->set('customer', new Customer());
        $this->registry->set('tax', new Tax($this->registry));
        $this->registry->set('currency', new Currency($languageAdaptor));
        $this->registry->set('weight', new Weight($this->registry));
        $this->registry->set('url', new Url());
        $this->registry->set('document', new Document());

        // Repositórios de Domínio
        $this->categoryRepository = new CategoryRepository($mapperFactory, $this->registry);
        $productRepository = $repositoryFactory->get(ProductRepository::class);
        $this->seoUrlRepository = $repositoryFactory->get(SeoUrlRepository::class);
        $this->informationRepository = $repositoryFactory->get(InformationRepository::class);
        $orderRepository = $repositoryFactory->get(OrderRepository::class);
        $cartRepository = $repositoryFactory->get(CartRepository::class);
        $sitemapRepository = $repositoryFactory->get(SitemapRepository::class);
        $manufacturerRepository = $repositoryFactory->get(ManufacturerRepository::class);
        $addressRepository = $repositoryFactory->get(AddressRepository::class);
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
            ->bind(AddressRepository::class, $addressRepository)
            ->bind(OrderReturnRepository::class, $orderReturnRepository)
            ->bind(Registry::class, $this->registry);
    }

    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    public function getContainer(): AppContainer
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
