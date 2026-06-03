<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;
use Alpha\Model\Domain\Repositories\AddressRepository;
use Psr\Container\ContainerInterface;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;
use Alpha\Support\Presenters\ImagePresenter;

class ShowProductAction implements ActionInterface
{
    private ProductRepository $productRepository;
    private SeoUrlRepository $seoRepository;
    private AddressRepository $addressRepository;
    private ContainerInterface $container;
    private TwigEnvironment $twig;

    public function __construct(
        ProductRepository $productRepository,
        SeoUrlRepository $seoRepository,
        AddressRepository $addressRepository,
        ContainerInterface $container,
        TwigEnvironment $twig
    ) {
        $this->productRepository = $productRepository;
        $this->seoRepository = $seoRepository;
        $this->addressRepository = $addressRepository;
        $this->container = $container;
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

        $config = $this->container->get('config');
        $currency = $this->container->get('currency');
        $tax = $this->container->get('tax');
        $session = $this->container->get('session');
        $currencyCode = $session->data['currency'] ?? ($config ? $config->get('config_currency') : 'BRL');
        $imagePresenter = new ImagePresenter($config ? $config->get('config_url') : null);

        // 1. Formatação Visual de Imagens (Principal e Adicionais)
        $product['popup'] = !empty($product['image']) ? $imagePresenter->resize($product['image'], $config ? (int)$config->get('config_image_popup_width') : 500, $config ? (int)$config->get('config_image_popup_height') : 500) : '';
        $product['thumb'] = !empty($product['image']) ? $imagePresenter->resize($product['image'], $config ? (int)$config->get('config_image_thumb_width') : 228, $config ? (int)$config->get('config_image_thumb_height') : 228) : '';

        if (!empty($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as &$img) {
                $img['popup'] = $imagePresenter->resize($img['image'] ?? '', $config ? (int)$config->get('config_image_popup_width') : 500, $config ? (int)$config->get('config_image_popup_height') : 500);
                $img['thumb'] = $imagePresenter->resize($img['image'] ?? '', $config ? (int)$config->get('config_image_additional_width') : 74, $config ? (int)$config->get('config_image_additional_height') : 74);
            }
            unset($img);
        }

        // 2. Formatação de Preços com Impostos integrados (Principal, Descontos e Opções)
        if ($currency && $tax && $config) {
            $taxClassId = (int)($product['tax_class_id'] ?? 0);
            $priceRaw = (float)($product['price'] ?? 0);
            $specialRaw = !empty($product['special']) ? (float)$product['special'] : false;

            $product['price_formatted'] = $currency->format($tax->calculate($priceRaw, $taxClassId, $config->get('config_tax')), $currencyCode);

            if ($specialRaw !== false && $specialRaw > 0) {
                $product['special_formatted'] = $currency->format($tax->calculate($specialRaw, $taxClassId, $config->get('config_tax')), $currencyCode);
                $product['tax_formatted'] = $config->get('config_tax') ? $currency->format($specialRaw, $currencyCode) : false;
            } else {
                $product['special_formatted'] = false;
                $product['tax_formatted'] = $config->get('config_tax') ? $currency->format($priceRaw, $currencyCode) : false;
            }

            if (!empty($product['discounts']) && is_array($product['discounts'])) {
                foreach ($product['discounts'] as &$discount) {
                    $discount['price_formatted'] = $currency->format($tax->calculate((float)$discount['price'], $taxClassId, $config->get('config_tax')), $currencyCode);
                }
                unset($discount);
            }

            if (!empty($product['options']) && is_array($product['options'])) {
                foreach ($product['options'] as &$option) {
                    if (!empty($option['product_option_value']) && is_array($option['product_option_value'])) {
                        foreach ($option['product_option_value'] as &$optionValue) {
                            if ((float)($optionValue['price'] ?? 0) > 0) {
                                $optionValue['price_formatted'] = $currency->format($tax->calculate((float)$optionValue['price'], $taxClassId, $config->get('config_tax')), $currencyCode);
                            } else {
                                $optionValue['price_formatted'] = false;
                            }
                        }
                        unset($optionValue);
                    }
                }
                unset($option);
            }
        }

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

                    // Formatação visual da miniatura
                    $relProd['thumb'] = $imagePresenter->resize($relProd['image'] ?? '', $config ? (int)$config->get('config_image_related_width') : 228, $config ? (int)$config->get('config_image_related_height') : 228);

                    // Formatação de Preços com Impostos integrados
                    if ($currency && $tax && $config) {
                        $relPriceBase = $tax->calculate((float)($relProd['price'] ?? 0), (int)($relProd['tax_class_id'] ?? 0), $config->get('config_tax'));
                        $relProd['price_formatted'] = $currency->format($relPriceBase, $currencyCode);

                        $relProd['special_formatted'] = !empty($relProd['special'])
                            ? $currency->format($tax->calculate((float)$relProd['special'], (int)($relProd['tax_class_id'] ?? 0), $config->get('config_tax')), $currencyCode)
                            : false;
                    }
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
        $customer = $this->container->get('customer');
        if ($customer && $customer->isLogged()) {
            $defaultAddress = $this->addressRepository->getDefaultAddress($customer->getId());
            if ($defaultAddress) {
                $shippingCep = preg_replace('/\D/', '', $defaultAddress->getPostcode());
            }
        }

        if (empty($shippingCep)) {
            $session = $this->container->get('session');
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
