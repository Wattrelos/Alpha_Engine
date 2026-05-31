<?php

namespace Alpha\Controller\Actions\Information;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\SitemapRepository;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;

class ShowSitemapAction implements ActionInterface
{
    private SitemapRepository $sitemapRepository;
    private TwigEnvironment $twig;

    public function __construct(
        SitemapRepository $sitemapRepository,
        TwigEnvironment $twig
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // 1. Obtém todos os dados consolidados do sitemap
        $sitemapData = $this->sitemapRepository->getSitemapData()->toArray();

        // 2. SEO tags
        $seoData = [
            'title'       => 'Mapa do Site | AgSonhos',
            'description' => 'Mapa do site completo da AgSonhos. Navegue por departamentos, categorias, páginas informativas e conta de usuário.',
            'keywords'    => 'mapa do site, sitemap, departamentos, categorias, institucional',
            'canonical'   => '/' . $request->getAttribute('language_code', 'pt-br') . '/mapa-do-site'
        ];

        // 3. Monta os breadcrumbs
        $breadcrumbs = [];
        $breadcrumbs[] = [
            'text' => 'Home',
            'href' => '/' . $request->getAttribute('language_code', 'pt-br')
        ];
        $breadcrumbs[] = [
            'text' => 'Mapa do Site',
            'href' => '/' . $request->getAttribute('language_code', 'pt-br') . '/mapa-do-site'
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
