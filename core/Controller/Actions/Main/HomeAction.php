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

/**
 * HomeAction — Página inicial pública.
 *
 * Usa o novo CategoryRepository (Slim, sem OpenCart Registry).
 * As categorias do menu são carregadas uma única vez como Twig global em index.php
 * e os destaques da home são carregados por esta Action.
 */
class HomeAction implements ActionInterface
{
    private TwigEnvironment     $twig;
    private CategoryRepository  $categoryRepository;
    private SettingRepository   $settingRepository;
    private LanguageRepository  $languageRepository;

    public function __construct(TwigEnvironment $twig, CategoryRepository $categoryRepository, SettingRepository $settingRepository, LanguageRepository $languageRepository)
    {
        $this->twig               = $twig;
        $this->categoryRepository = $categoryRepository;
        $this->settingRepository  = $settingRepository;
        $this->languageRepository = $languageRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
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

        $html = $this->twig->render('home.html.twig', [
            'direction'           => 'ltr',
            'lang'                => $language ? $language->getCode() : 'pt-br',
            'title'               => $configSettings['config_meta_title'] ?? 'Início | AgSonhos',
            'description'         => $configSettings['config_meta_description'] ?? '',
            'keywords'            => $configSettings['config_meta_keyword'] ?? '',
            'language'            => $language,
            'featured_categories' => $featuredCategories,
            'settings'            => $configSettings,
            'logo'                => $logo,
            'name'                => $configSettings['config_name'] ?? 'AG Sonhos e Construções',
            'home'                => '/',
            // Próximas expansões:
            // 'featured_products' => $this->productRepository->getFeatured(),
            // 'banners'           => $this->bannerRepository->getActive(),
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
