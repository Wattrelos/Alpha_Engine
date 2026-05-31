<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Support\Registry;
use Alpha\Model\Domain\Repositories\PriceRepository;
use Alpha\Model\Domain\Repositories\ProductOptionValueRepository;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Support\Presenters\ImagePresenter;

/**
 * CalculateVisitorCartAction - Processa a API de cálculo de carrinho do visitante
 * 
 * Resolve os preços e opções de produtos contidos no localStorage do visitante,
 * retornando a resposta formatada em JSON.
 */
class CalculateVisitorCartAction implements ActionInterface
{
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = json_decode($request->getBody()->getContents(), true);
        $items = $body['items'] ?? [];

        $products = [];
        $subtotal = 0;

        $repositoryFactory = $this->registry->get('alpha_repository_factory');
        $mapperFactory = $this->registry->get('alpha_mapper_factory');

        $priceRepository = $repositoryFactory->get(PriceRepository::class);
        $productMapper = $mapperFactory->get(ProductMapper::class);
        $optionValueRepo = $repositoryFactory->get(ProductOptionValueRepository::class);

        $langId = (int)$this->registry->get('config')->get('config_language_id') ?: 2;
        $storeId = 0;
        $customerGroupId = 1;
        $priceStatements = $priceRepository->getPriceStatements($customerGroupId);

        $currency = $this->registry->get('currency');
        $currencyCode = $_SESSION['currency'] ?? 'BRL';

        $productIds = array_unique(array_column($items, 'product_id'));
        $productDataMap = [];
        if (!empty($productIds)) {
            $productDataMap = $productMapper->getProductsByIds($productIds, $langId, $storeId, $customerGroupId, $priceStatements);
            $productDataMap = array_column($productDataMap, null, 'id');
        }

        $imagePresenter = new ImagePresenter($this->registry);

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];
            $options = $item['option'] ?? [];

            $productInfo = $productDataMap[$productId] ?? null;
            if (!$productInfo) continue;

            $basePrice = (float)$productInfo['price'];
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
            $totalPrice = $unitPrice * $quantity;
            $subtotal += $totalPrice;

            $thumb = $imagePresenter->resize($productInfo['image'], 80, 80);

            $optionStr = json_encode($options);
            // Base64 safe string representation
            $cartKey = $productId . '_' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($optionStr));

            $products[] = [
                'cart_id'    => $cartKey,
                'product_id' => $productId,
                'thumb'      => $thumb,
                'name'       => $productInfo['name'],
                'model'      => $productInfo['model'],
                'option'     => $optionData,
                'quantity'   => $quantity,
                'option_raw' => $optionStr,
                'price'      => $currency->format($unitPrice, $currencyCode),
                'total'      => $currency->format($totalPrice, $currencyCode),
                'href'       => '/' . $this->registry->get('config')->get('config_language') . '/produto/' . ($productInfo['keyword'] ?? $productId)
            ];
        }

        $totals = [
            [
                'title' => 'Sub-Total',
                'text'  => $currency->format($subtotal, $currencyCode)
            ],
            [
                'title' => 'Total',
                'text'  => $currency->format($subtotal, $currencyCode)
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
