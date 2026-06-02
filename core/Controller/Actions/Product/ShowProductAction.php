<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Alpha\Model\Domain\Repositories\AddressRepository;
use Alpha\Support\Registry;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;

class ShowProductAction implements ActionInterface
{
    private ProductRepository $productRepository;
    private SeoUrlRepository $seoRepository;
    private AddressRepository $addressRepository;
    private Registry $registry;
    private TwigEnvironment $twig;

    public function __construct(
        ProductRepository $productRepository,
        SeoUrlRepository $seoRepository,
        AddressRepository $addressRepository,
        Registry $registry,
        TwigEnvironment $twig
    ) {
        $this->productRepository = $productRepository;
        $this->seoRepository = $seoRepository;
        $this->addressRepository = $addressRepository;
        $this->registry = $registry;
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

        // Mapeamento inteligente de URLs amigáveis (SEO) para os produtos relacionados.
        // Varre as chaves comuns ('related' ou 'related_products') para hidratar os links.
        $relatedKeys = ['related', 'related_products'];
        foreach ($relatedKeys as $relKey) {
            if (isset($product[$relKey]) && is_array($product[$relKey])) {
                foreach ($product[$relKey] as &$relProd) {
                    $relId = (int)($relProd['product_id'] ?? $relProd['id'] ?? 0);
                    $keyword = $relId > 0 ? $this->seoRepository->getKeywordByQuery('product_id', $relId, 0, $languageId) : '';
                    
                    $relSlug = !empty($keyword) ? $keyword : (!empty($relProd['keyword']) ? $relProd['keyword'] : $relId);
                    $relProd['slug'] = $relSlug;
                    $relProd['href'] = $routeParser->urlFor('product.detail', ['lang' => $lang, 'slug' => (string)$relSlug]);
                }
                unset($relProd);
            }
        }

        // SEO tags e cabeçalhos
        $seoData = [
            'title'       => ($product['meta_title'] ?? $product['name']) . ' | AgSonhos',
            'description' => $product['meta_description'] ?? 'Confira os detalhes de nossos produtos.',
            'keywords'    => $product['meta_keyword'] ?? '',
            'image'       => $product['popup'] ?? '',
            'canonical'   => $routeParser->urlFor('product.detail', ['lang' => $lang, 'slug' => $slug])
        ];

        // Tenta recuperar o CEP do cliente logado ou da sessão
        $shippingCep = '';
        $customer = $this->registry->get('customer');
        if ($customer && $customer->isLogged()) {
            $defaultAddress = $this->addressRepository->getDefaultAddress($customer->getId());
            if ($defaultAddress) {
                $shippingCep = preg_replace('/\D/', '', $defaultAddress->getPostcode());
            }
        }

        if (empty($shippingCep)) {
            $session = $this->registry->get('session');
            if ($session && !empty($session->data['shipping_address']['postcode'])) {
                $shippingCep = preg_replace('/\D/', '', $session->data['shipping_address']['postcode']);
            }
        }

        $html = $this->twig->render('pages/product/show.html.twig', [
            'product'      => $product,
            'seo'          => $seoData,
            'title'        => $seoData['title'],
            'description'  => $seoData['description'],
            'keywords'     => $seoData['keywords'],
            'shipping_cep' => $shippingCep,
            'lang'         => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
