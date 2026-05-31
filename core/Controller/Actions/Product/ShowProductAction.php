<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;

class ShowProductAction implements ActionInterface
{
    private ProductRepository $productRepository;
    private SeoUrlRepository $seoRepository;
    private TwigEnvironment $twig;

    public function __construct(
        ProductRepository $productRepository,
        SeoUrlRepository $seoRepository,
        TwigEnvironment $twig
    ) {
        $this->productRepository = $productRepository;
        $this->seoRepository = $seoRepository;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'] ?? '';
        $languageId = $request->getAttribute('language_id', 2);
        $productId = 0;

        // Se o slug for numérico, assumimos que é o ID do produto diretamente (fallback)
        if (is_numeric($slug)) {
            $productId = (int)$slug;
        } else {
            // Caso contrário, resolvemos via tabela seo_url
            $queryStr = $this->seoRepository->getQueryByKeyword($slug, 0, $languageId);
            if (!empty($queryStr)) {
                parse_str($queryStr, $resolvedParams);
                if (isset($resolvedParams['product_id'])) {
                    $productId = (int)$resolvedParams['product_id'];
                }
            }
        }

        // Busca os dados consolidados do produto
        $product = null;
        if ($productId > 0) {
            $product = $this->productRepository->getProductDisplayData($productId);
        }

        if (!$product) {
            // Renderiza 404 caso o produto não exista
            $html404 = $this->twig->render('pages/errors/404.html.twig', [
                'title'       => 'Produto Não Encontrado | AgSonhos',
                'description' => 'O produto solicitado não foi encontrado em nosso catálogo.',
            ]);
            $response->getBody()->write($html404);
            return $response->withStatus(404);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        // SEO tags e cabeçalhos
        $seoData = [
            'title'       => ($product['meta_title'] ?? $product['name']) . ' | AgSonhos',
            'description' => $product['meta_description'] ?? 'Confira os detalhes de nossos produtos.',
            'keywords'    => $product['meta_keyword'] ?? '',
            'image'       => $product['popup'] ?? '',
            'canonical'   => $routeParser->urlFor('product.detail', ['lang' => $lang, 'slug' => $slug])
        ];

        $html = $this->twig->render('pages/product/show.html.twig', [
            'product'     => $product,
            'seo'         => $seoData,
            'title'       => $seoData['title'],
            'description' => $seoData['description'],
            'keywords'    => $seoData['keywords']
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}

