<?php

namespace Alpha\Controller\Actions\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Entities\Language;
use Slim\Routing\RouteContext;

/**
 * HomeAction — Página inicial pública.
 *
 * Usa o novo CategoryRepository (Slim, sem o código legado Registry).
 * As categorias do menu são carregadas uma única vez como Twig global em index.php
 * e os destaques da home são carregados por esta Action.
 */
class HomeAction implements ActionInterface
{
    private TwigEnvironment     $twig;
    private CategoryRepository  $categoryRepository;
    private SettingRepository   $settingRepository;
    private LanguageRepository  $languageRepository;
    private \Alpha\Support\Language $translator;

    public function __construct(
        TwigEnvironment $twig,
        CategoryRepository $categoryRepository,
        SettingRepository $settingRepository,
        LanguageRepository $languageRepository,
        \Alpha\Support\Language $translator
    ) {
        $this->twig               = $twig;
        $this->categoryRepository = $categoryRepository;
        $this->settingRepository  = $settingRepository;
        $this->languageRepository = $languageRepository;
        $this->translator         = $translator;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Carrega o namespace 'home' e disponibiliza no Twig
        $this->translator->load('home');
        $this->twig->addGlobal('Home', $this->translator->getNestedData('home'));

        // Categorias em destaque para a grade visual da home page
        // (as categorias do menu já estão disponíveis como Twig global via index.php)
        $featuredCategories = $this->categoryRepository->getFeaturedCategories();

        $configSettings = $this->settingRepository->getSetting('config', 0);
        $languageCode   = $configSettings['config_language_catalog'] ?? 'pt-br';
        /** @var Language|null $language */
        $language       = $this->languageRepository->getByCode($languageCode);

        if (!$language) {
            /** @var Language|null $language */
            $language = $this->languageRepository->find(2);
        }

        $logo = '';
        if (!empty($configSettings['config_logo'])) {
            $logo = HTTP_SERVER . 'img/' . $configSettings['config_logo'];
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $storeSettingsHelper = new \Alpha\Support\StoreSettings($configSettings, $language ? $language->getId() : 2);
        $storeData = $storeSettingsHelper->getFormattedSettings();

        $html = $this->twig->render('home.html.twig', [
            'direction'           => 'ltr',
            'lang'                => $language ? $language->getCode() : 'pt-br',
            'title'               => $storeData['metaTitle'],
            'description'         => $storeData['metaDescription'],
            'keywords'            => $storeData['metaKeyword'],
            'language'            => $language,
            'featured_categories' => $featuredCategories,
            'settings'            => $configSettings,
            'store'               => $storeData,
            'logo'                => $storeData['logo'],
            'name'                => $storeData['name'],
            'home'                => $routeParser->urlFor('home', ['lang' => $lang]),
            // Próximas expansões:
            // 'featured_products' => $this->productRepository->getFeatured(),
            // 'banners'           => $this->bannerRepository->getActive(),
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
