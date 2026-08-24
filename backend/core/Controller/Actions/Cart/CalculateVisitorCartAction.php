<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Container\ContainerInterface;
use Alpha\Model\Domain\Repositories\PriceRepository;
use Alpha\Model\Domain\Repositories\ProductOptionValueRepository;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Support\Presenters\ImagePresenter;
use Slim\Routing\RouteContext;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Mappers\MapperFactory;

/**
 * CalculateVisitorCartAction - Processa a API de cálculo de carrinho do visitante
 * 
 * Resolve os preços e opções de produtos contidos no localStorage do visitante,
 * retornando a resposta formatada em JSON.
 */
class CalculateVisitorCartAction implements ActionInterface
{
    private ContainerInterface $container;
    private ImagePresenter $imagePresenter;

    public function __construct(ContainerInterface $container, ImagePresenter $imagePresenter)
    {
        $this->container = $container;
        $this->imagePresenter = $imagePresenter;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $raw = (string)$request->getBody();
            $body = !empty($raw) ? json_decode($raw, true) : [];
        }
        $items = is_array($body) ? ($body['items'] ?? []) : [];

        $products = [];
        $subtotal = 0;
        $taxAmount = 0.0;

        $priceRepository = RepositoryFactory::getInstance()->get(PriceRepository::class);
        $optionValueRepo = RepositoryFactory::getInstance()->get(ProductOptionValueRepository::class);
        $productMapper   = MapperFactory::getInstance()->get(ProductMapper::class);
        $discountRepo    = RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\ProductDiscountRepository::class);
        $seoRepository   = RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\SeoUrlRepository::class);

        $config = $this->container->has('config') ? $this->container->get('config') : null;
        $langId = $config ? (int)$config->get('config_language_id') : 2;
        $storeId = $config && $config->get('config_store_id') !== null ? (int)$config->get('config_store_id') : 1;
        if ($storeId <= 0) {
            $storeId = 1;
        }
        $customerGroupId = 1;
        $priceStatements = $priceRepository->getPriceStatements($customerGroupId);

        $currency = $this->container->has('currency') ? $this->container->get('currency') : null;
        $currencyCode = $_SESSION['currency'] ?? 'BRL';

        $productIds = array_unique(array_column($items, 'product_id'));
        $productDataMap = [];
        if (!empty($productIds)) {
            $productDataMap = $productMapper->getProductsByIds($productIds, $langId, $storeId, $customerGroupId, $priceStatements);
            $productDataMap = array_column($productDataMap, null, 'id');

            // Identifica e carrega em lote os produtos pai para variações (Evita N+1)
            $parentIdsToFetch = [];
            foreach ($productDataMap as $prod) {
                $masterId = (int)($prod['master_id'] ?? 0);
                if ($masterId > 0 && !isset($productDataMap[$masterId])) {
                    $parentIdsToFetch[] = $masterId;
                }
            }

            if (!empty($parentIdsToFetch)) {
                $parentDataMap = $productMapper->getProductsByIds($parentIdsToFetch, $langId, $storeId, $customerGroupId, $priceStatements);
                foreach ($parentDataMap as $parentProd) {
                    $productDataMap[$parentProd['id']] = $parentProd;
                }
            }

            // Hidratação e Herança das variações
            foreach ($productDataMap as $id => &$prod) {
                $masterId = (int)($prod['master_id'] ?? 0);
                if ($masterId > 0 && isset($productDataMap[$masterId])) {
                    $parent = $productDataMap[$masterId];

                    // Preço Base
                    if ((float)$prod['price'] <= 0.0) {
                        $prod['price'] = $parent['price'];
                    }

                    // Preço Promocional (Special e Discount)
                    if (!isset($prod['special']) || (float)$prod['special'] <= 0.0) {
                        if ((float)$prod['price'] === (float)$parent['price']) {
                            $prod['special'] = $parent['special'] ?? null;
                        }
                    }
                    if (!isset($prod['discount']) || (float)$prod['discount'] <= 0.0) {
                        if ((float)$prod['price'] === (float)$parent['price']) {
                            $prod['discount'] = $parent['discount'] ?? null;
                        }
                    }

                    // Classe de Imposto
                    if (empty($prod['tax_class_id'])) {
                        $prod['tax_class_id'] = $parent['tax_class_id'];
                    }

                    // Peso e Classe de Peso
                    if ((float)$prod['weight'] <= 0.0) {
                        $prod['weight'] = $parent['weight'];
                        $prod['weight_class_id'] = $parent['weight_class_id'];
                    }

                    // Imagem
                    if (empty($prod['image'])) {
                        $prod['image'] = $parent['image'];
                    }

                    // Pontos
                    if (empty($prod['points'])) {
                        $prod['points'] = $parent['points'];
                    }
                    if (empty($prod['reward'])) {
                        $prod['reward'] = $parent['reward'] ?? 0;
                    }

                    // Compra Mínima
                    if (empty($prod['minimum']) || (int)$prod['minimum'] <= 1) {
                        $prod['minimum'] = $parent['minimum'];
                    }

                    // Estoque Subtraível
                    if (isset($parent['subtract'])) {
                        $prod['subtract'] = $parent['subtract'];
                    }

                    // Frete Requerido
                    if (isset($parent['shipping'])) {
                        $prod['shipping'] = $parent['shipping'];
                    }
                }
            }
            unset($prod);
        }

        $imagePresenter = $this->imagePresenter;

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $tax = $this->container->has('tax') ? $this->container->get('tax') : null;
        if ($tax && isset($_SESSION['shipping_address'])) {
            $shippingAddr = $_SESSION['shipping_address'];
            $countryId = (int)($shippingAddr['country_id'] ?? 76);
            $zoneId = (int)($shippingAddr['zone_id'] ?? 0);
            $tax->setShippingAddress($countryId, $zoneId);
            $tax->setPaymentAddress($countryId, $zoneId);
        }

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];
            $options = $item['option'] ?? [];

            $productInfo = $productDataMap[$productId] ?? null;
            if (!$productInfo) continue;

            // progressive discount calculation
            $activeDiscounts = $discountRepo->getActiveDiscounts($productInfo['id'], $customerGroupId);
            $discountPrice = null;

            foreach ($activeDiscounts as $discount) {
                if ($quantity >= $discount->getQuantity()) {
                    if ($discountPrice === null || $discount->getPrice() < $discountPrice) {
                        $discountPrice = $discount->getPrice();
                    }
                }
            }

            $basePrice = (float)$productInfo['price'];
            if ((float)($productInfo['special'] ?? 0)) {
                $basePrice = (float)$productInfo['special'];
            } elseif ($discountPrice !== null) {
                $basePrice = (float)$discountPrice;
            }

            $optionPrice = 0.0;
            $optionData = [];

            foreach ($options as $productOptionId => $value) {
                if (is_scalar($value)) {
                    $optionValueId = (int)$value;
                    $optionValueEntity = $optionValueRepo->find($optionValueId);
                    if ($optionValueEntity) {
                        $optionDetails = $productMapper->getOptionValuesByIds([$optionValueId], $langId);
                        $optDetail = $optionDetails[$optionValueId] ?? null;
                        if ($optDetail) {
                            $optionData[] = [
                                'name'  => $optDetail['option_name'],
                                'value' => $optDetail['name']
                            ];
                        }

                        $optPrice = (float)$optionValueEntity->getPrice();
                        if ($optionValueEntity->getPricePrefix() === '-') {
                            $optionPrice -= $optPrice;
                        } else {
                            $optionPrice += $optPrice;
                        }
                    }
                }
            }

            $unitPrice = $basePrice + $optionPrice;
            $taxClassId = (int)($productInfo['tax_class_id'] ?? 0);
            $taxPrice = ($tax && $config) ? $tax->calculate($unitPrice, $taxClassId, $config->get('config_tax')) : $unitPrice;

            $totalPrice = $unitPrice * $quantity;
            $taxTotalPrice = $taxPrice * $quantity;

            $subtotal += $totalPrice;

            if ($tax && $config && $productInfo['tax_class_id']) {
                $taxRates = $tax->getRates($unitPrice, $productInfo['tax_class_id']);
                foreach ($taxRates as $taxRate) {
                    $taxAmount += $taxRate['amount'] * $quantity;
                }
            }

            $thumb = $imagePresenter->resize($productInfo['image'], 80, 80);

            $optionStr = json_encode($options);
            // Base64 safe string representation
            $cartKey = $productId . '_' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($optionStr));

            $displayId = (int)($productInfo['master_id'] ?? 0) > 0 ? (int)$productInfo['master_id'] : $productId;
            $keyword = $seoRepository->getKeywordByQuery('product_id', $displayId, $storeId, $langId);
            $slug = !empty($keyword) ? $keyword : $displayId;

            $products[] = [
                'cart_id'        => $cartKey,
                'product_id'     => $productId,
                'master_id'      => (int)($productInfo['master_id'] ?? 0),
                'thumb'          => $thumb,
                'name'       => $productInfo['name'],
                'model'      => $productInfo['model'],
                'option'     => $optionData,
                'quantity'   => $quantity,
                'stock_quantity' => (int)$productInfo['quantity'],
                'option_raw' => $optionStr,
                'price'      => $currency ? $currency->format($taxPrice, $currencyCode) : 'R$ ' . number_format($taxPrice, 2, ',', '.'),
                'total'      => $currency ? $currency->format($taxTotalPrice, $currencyCode) : 'R$ ' . number_format($taxTotalPrice, 2, ',', '.'),
                'href'       => $routeParser->urlFor('product.detail', ['lang' => $lang, 'slug' => (string)$slug])
            ];
        }

        $grandTotal = $subtotal + $taxAmount;

        $totals = [
            [
                'title' => 'Sub-Total',
                'text'  => $currency ? $currency->format($subtotal, $currencyCode) : 'R$ ' . number_format($subtotal, 2, ',', '.')
            ],
            [
                'title' => 'Total',
                'text'  => $currency ? $currency->format($grandTotal, $currencyCode) : 'R$ ' . number_format($grandTotal, 2, ',', '.')
            ]
        ];

        $response->getBody()->write(json_encode([
            'products' => $products,
            'totals'   => $totals,
            'success'  => true
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
