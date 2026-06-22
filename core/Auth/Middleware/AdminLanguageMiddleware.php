<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Psr\Container\ContainerInterface;
use Twig\Environment as TwigEnvironment;

use Slim\Routing\RouteContext;

class AdminLanguageMiddleware
{
    private const ROUTE_NAMESPACE_MAP = [
        'admin.supplier.list'   => 'admin/supplier',
        'admin.supplier.create' => 'admin/supplier',
        'admin.supplier.store'  => 'admin/supplier',
        'admin.supplier.edit'   => 'admin/supplier',
        'admin.supplier.update' => 'admin/supplier',
        'admin.supplier.delete' => 'admin/supplier',
        'admin.product.list'    => 'admin/product',
        'admin.product.create'  => 'admin/product',
        'admin.product.edit'    => 'admin/product',
        'admin.product.update'  => 'admin/product',
        'admin.product.delete'  => 'admin/product',
        'admin.category.list'   => 'admin/category',
        'admin.category.create' => 'admin/category',
        'admin.category.edit'   => 'admin/category',
        'admin.category.update' => 'admin/category',
        'admin.category.delete' => 'admin/category',
    ];

    private LanguageRepository $languageRepository;
    private TwigEnvironment $twig;
    private ContainerInterface $container;

    public function __construct(LanguageRepository $languageRepository, TwigEnvironment $twig, ContainerInterface $container)
    {
        $this->languageRepository = $languageRepository;
        $this->twig = $twig;
        $this->container = $container;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $cookies = $request->getCookieParams();
        $langCode = $cookies['admin_language'] ?? 'pt-br';

        // Normalização de fr-fr para fr para consulta no banco de dados
        $dbCode = ($langCode === 'fr-fr') ? 'fr' : $langCode;

        // Fetch language by code from repository
        $language = $this->languageRepository->getByCode($dbCode);
        if (!$language || !$language->getStatus()) {
            // Fallback to pt-br (ID 2)
            $language = $this->languageRepository->find(2);
        }

        $languageId = $language ? $language->getId() : 2;
        $langCode = $language ? $language->getCode() : 'pt-br';

        // Normalização de fr para fr-fr para o tradutor e views
        $translatorCode = ($langCode === 'fr') ? 'fr-fr' : $langCode;

        // Update container translator code
        $translator = $this->container->has('language') ? $this->container->get('language') : null;
        if ($translator && method_exists($translator, 'setCode')) {
            $translator->setCode($translatorCode);
        }

        // Load admin common translations and add as global variable
        if ($translator && method_exists($translator, 'load')) {
            $translator->load('admin/common');
            $adminLangData = [];
            if (method_exists($translator, 'getNestedData')) {
                $adminLangData = $translator->getNestedData('admin/common') ?: [];
            }

            // Exemplo de rota: resolve o nome e mescla as traduções da página se mapeado
            $routeName = null;
            try {
                $routeContext = RouteContext::fromRequest($request);
                $route = $routeContext->getRoute();
                $routeName = $route ? $route->getName() : null;
            } catch (\RuntimeException $e) {
                // Ignore route context issues if route hasn't been set (e.g. in tests/early phases)
            }

            if ($routeName && isset(self::ROUTE_NAMESPACE_MAP[$routeName])) {
                $namespace = self::ROUTE_NAMESPACE_MAP[$routeName];
                $translator->load($namespace);
                if (method_exists($translator, 'getNestedData')) {
                    $pageData = $translator->getNestedData($namespace) ?: [];
                    $adminLangData = array_replace_recursive($adminLangData, $pageData);
                }
            }

            $this->twig->addGlobal('AdminLang', $adminLangData);
        }

        // Fetch all active languages for the switcher UI
        $activeLanguages = [];
        try {
            $languages = $this->languageRepository->findBy(['status' => 1]);
            foreach ($languages as $langEntity) {
                $code = $langEntity->getCode();
                if ($code === 'fr') {
                    $code = 'fr-fr';
                }
                $activeLanguages[] = [
                    'id'   => $langEntity->getId(),
                    'code' => $code,
                    'name' => $langEntity->getName(),
                ];
            }
        } catch (\Throwable $e) {
            // Fallback to defaults if repo fails
            $activeLanguages = [
                ['id' => 2, 'code' => 'pt-br', 'name' => 'Português'],
                ['id' => 1, 'code' => 'en-gb', 'name' => 'English'],
                ['id' => 3, 'code' => 'fr-fr', 'name' => 'Français']
            ];
        }

        // Bind global twig variables
        $this->twig->addGlobal('languages', $activeLanguages);
        $this->twig->addGlobal('admin_lang_code', $translatorCode);

        // Bind request attributes for actions consumption
        $request = $request->withAttribute('language_id', $languageId)
                           ->withAttribute('language_code', $translatorCode);

        return $handler->handle($request);
    }
}
