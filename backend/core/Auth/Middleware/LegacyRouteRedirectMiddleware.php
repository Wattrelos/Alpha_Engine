<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;

class LegacyRouteRedirectMiddleware
{
    private SeoUrlRepository $seoRepository;

    public function __construct(SeoUrlRepository $seoRepository)
    {
        $this->seoRepository = $seoRepository;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $queryParams = $request->getQueryParams();

        // Se contiver o parâmetro 'route', é uma rota antigado código legado
        if (isset($queryParams['route'])) {
            $route = $queryParams['route'];
            $lang = $queryParams['language'] ?? 'pt-br';

            // Normaliza o idioma para os suportados
            if ($lang !== 'pt-br' && $lang !== 'en' && $lang !== 'es') {
                $lang = 'pt-br';
            }

            $redirectUrl = null;
            $storeId = 0; // Loja padrão
            $languageId = ($lang === 'en') ? 1 : 2; // 1 para en, 2 para pt-br (id legado no banco)

            switch ($route) {
                case 'common/home':
                    $redirectUrl = "/{$lang}";
                    break;

                case 'account/login':
                    $redirectUrl = "/{$lang}/login";
                    break;

                case 'account/register':
                    $redirectUrl = "/{$lang}/cadastro";
                    break;

                case 'account/logout':
                    $redirectUrl = "/{$lang}/logout";
                    break;

                case 'checkout/cart':
                    $redirectUrl = "/{$lang}/carrinho";
                    break;

                case 'checkout/checkout':
                    $redirectUrl = "/{$lang}/checkout";
                    break;

                case 'product/product':
                    if (isset($queryParams['product_id'])) {
                        $productId = (int)$queryParams['product_id'];
                        $keyword = $this->seoRepository->getKeywordByQuery('product_id', (string)$productId, $storeId, $languageId);
                        if ($keyword) {
                            $redirectUrl = "/{$lang}/produto/{$keyword}";
                        } else {
                            $redirectUrl = "/{$lang}/produto/{$productId}";
                        }
                    }
                    break;

                case 'product/category':
                    if (isset($queryParams['path'])) {
                        $parts = explode('_', $queryParams['path']);
                        $categoryId = (int)end($parts);
                        $keyword = $this->seoRepository->getKeywordByQuery('category_id', (string)$categoryId, $storeId, $languageId);
                        if ($keyword) {
                            $redirectUrl = "/{$lang}/categoria/{$keyword}";
                        } else {
                            $redirectUrl = "/{$lang}/categoria/{$categoryId}";
                        }
                    }
                    break;

                case 'information/information':
                    if (isset($queryParams['information_id'])) {
                        $infoId = (int)$queryParams['information_id'];
                        $keyword = $this->seoRepository->getKeywordByQuery('information_id', (string)$infoId, $storeId, $languageId);
                        if ($keyword) {
                            $redirectUrl = "/{$lang}/pagina/{$keyword}";
                        } else {
                            $redirectUrl = "/{$lang}/pagina/{$infoId}";
                        }
                    }
                    break;

                case 'product/search':
                    $searchQuery = isset($queryParams['search']) ? '?busca=' . urlencode($queryParams['search']) : '';
                    $redirectUrl = "/{$lang}/busca{$searchQuery}";
                    break;
            }

            if ($redirectUrl !== null) {
                $response = new SlimResponse();
                return $response
                    ->withHeader('Location', $redirectUrl)
                    ->withStatus(301); // Redirecionamento permanente para SEO
            }
        }

        return $handler->handle($request);
    }
}
