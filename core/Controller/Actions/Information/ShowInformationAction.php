<?php

namespace Alpha\Controller\Actions\Information;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\InformationRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;

class ShowInformationAction implements ActionInterface
{
    private InformationRepository $informationRepository;
    private SeoUrlRepository $seoRepository;
    private TwigEnvironment $twig;

    public function __construct(
        InformationRepository $informationRepository,
        SeoUrlRepository $seoRepository,
        TwigEnvironment $twig
    ) {
        $this->informationRepository = $informationRepository;
        $this->seoRepository = $seoRepository;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'] ?? '';
        $languageId = $request->getAttribute('language_id', 2);
        $informationId = 0;

        // Se o slug for numérico, assume que é o ID diretamente
        if (is_numeric($slug)) {
            $informationId = (int)$slug;
        } else {
            // Resolve via seo_url
            $queryStr = $this->seoRepository->getQueryByKeyword($slug, 1, $languageId);
            if (!empty($queryStr)) {
                parse_str($queryStr, $resolvedParams);
                if (isset($resolvedParams['information_id'])) {
                    $informationId = (int)$resolvedParams['information_id'];
                }
            }
        }

        if ($informationId <= 0) {
            return $this->render404($response);
        }

        // Obtém os dados consolidados da página institucional
        $viewResponse = $this->informationRepository->getInformationDisplayData($informationId);
        if (!$viewResponse) {
            return $this->render404($response);
        }

        $data = $viewResponse->getData();

        // Converte a descrição em Markdown para HTML usando Parsedown
        $parsedown = new \Parsedown();
        if (!empty($data['description'])) {
            $data['description'] = $parsedown->text($data['description']);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        // SEO tags
        $seoData = [
            'title'       => ($data['meta_title'] ?? $data['title']) . ' | AgSonhos',
            'description' => $data['meta_description'] ?? 'Páginas informativas da AgSonhos.',
            'keywords'    => $data['meta_keyword'] ?? '',
            'canonical'   => $routeParser->urlFor('info.page', ['lang' => $lang, 'slug' => $slug])
        ];

        $html = $this->twig->render('pages/information/show.html.twig', [
            'information' => $data,
            'seo'         => $seoData,
            'title'       => $seoData['title'],
            'description' => $seoData['description'],
            'keywords'    => $seoData['keywords']
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    private function render404(Response $response): Response
    {
        $html404 = $this->twig->render('pages/errors/404.html.twig', [
            'title'       => 'Página Não Encontrada | AgSonhos',
            'description' => 'A página solicitada não foi encontrada.',
        ]);
        $response->getBody()->write($html404);
        return $response->withStatus(404);
    }
}

