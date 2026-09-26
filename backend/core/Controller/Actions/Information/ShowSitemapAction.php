<?php

namespace Alpha\Controller\Actions\Information;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\SitemapRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;

class ShowSitemapAction implements ActionInterface
{
    private SitemapRepository $sitemapRepository;
    private SeoUrlRepository $seoRepository;
    private TwigEnvironment $twig;
    private \Alpha\Support\Language $translator;

    public function __construct(
        SitemapRepository $sitemapRepository,
        SeoUrlRepository $seoRepository,
        TwigEnvironment $twig,
        \Alpha\Support\Language $translator
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->seoRepository = $seoRepository;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');
        $languageId = $request->getAttribute('language_id', 2);

        // Carrega as traduções do sitemap
        $this->translator->load('information/sitemap');
        $this->twig->addGlobal('Sitemap', $this->translator->getNestedData('information/sitemap'));

        // 1. Obtém todos os dados consolidados do sitemap
        $sitemapData = $this->sitemapRepository->getSitemapData()->toArray();

        // Resolvendo URLs de categorias amigáveis recursivamente
        $resolveCategoryUrls = function (array $categories) use ($routeParser, $lang, $languageId, &$resolveCategoryUrls) {
            foreach ($categories as &$cat) {
                $catId = (int)($cat['id'] ?? 0);
                if ($catId > 0) {
                    $keyword = $this->seoRepository->getKeywordByQuery('category_id', $catId, 0, $languageId);
                    $cat['href'] = $routeParser->urlFor('category.detail', ['lang' => $lang, 'slug' => (string)(!empty($keyword) ? $keyword : $catId)]);
                }
                if (!empty($cat['children']) && is_array($cat['children'])) {
                    $cat['children'] = $resolveCategoryUrls($cat['children']);
                }
            }
            return $categories;
        };

        if (isset($sitemapData['categories']) && is_array($sitemapData['categories'])) {
            $sitemapData['categories'] = $resolveCategoryUrls($sitemapData['categories']);
        }

        // Resolvendo URLs de páginas de informação institucionais
        if (isset($sitemapData['informations']) && is_array($sitemapData['informations'])) {
            foreach ($sitemapData['informations'] as &$info) {
                $infoId = (int)($info['id'] ?? 0);
                if ($infoId > 0) {
                    $keyword = $this->seoRepository->getKeywordByQuery('information_id', $infoId, 0, $languageId);
                    $info['href'] = $routeParser->urlFor('info.page', ['lang' => $lang, 'slug' => (string)(!empty($keyword) ? $keyword : $infoId)]);
                }
            }
            unset($info);
        }

        // Resolvendo URLs estáticas e de conta
        $sitemapData['special']  = $routeParser->urlFor('home', ['lang' => $lang]); // fallback to home as product/special doesn't exist
        $sitemapData['account']  = $routeParser->urlFor('account.index', ['lang' => $lang]);
        $sitemapData['edit']     = $routeParser->urlFor('account.edit', ['lang' => $lang]);
        $sitemapData['password'] = $routeParser->urlFor('account.resetar-senha.logged', ['lang' => $lang]);
        $sitemapData['address']  = $routeParser->urlFor('account.addresses', ['lang' => $lang]);
        $sitemapData['history']  = $routeParser->urlFor('account.orders', ['lang' => $lang]);
        $sitemapData['cart']     = $routeParser->urlFor('cart.index', ['lang' => $lang]);
        $sitemapData['checkout'] = $routeParser->urlFor('checkout.index', ['lang' => $lang]);
        $sitemapData['search']   = $routeParser->urlFor('search', ['lang' => $lang]);
        $sitemapData['contact']  = $routeParser->urlFor('contact', ['lang' => $lang]);

        // 2. SEO tags
        $seoData = [
            'title'       => 'Mapa do Site | meusite',
            'description' => 'Mapa do site completo da meusite. Navegue por departamentos, categorias, páginas informativas e conta de usuário.',
            'keywords'    => 'mapa do site, sitemap, departamentos, categorias, institucional',
            'canonical'   => $routeParser->urlFor('sitemap', ['lang' => $lang])
        ];

        // 3. Monta os breadcrumbs
        $breadcrumbs = [];
        $breadcrumbs[] = [
            'text' => 'Home',
            'href' => $routeParser->urlFor('home', ['lang' => $lang])
        ];
        $breadcrumbs[] = [
            'text' => 'Mapa do Site',
            'href' => $routeParser->urlFor('sitemap', ['lang' => $lang])
        ];

        // 4. Combina os dados para o template
        $templateData = array_merge($sitemapData, [
            'breadcrumbs'   => $breadcrumbs,
            'seo'           => $seoData,
            'heading_title' => 'Mapa do Site'
        ]);

        $html = $this->twig->render('pages/information/sitemap.twig', $templateData);

        $response->getBody()->write($html);
        return $response;
    }
}

