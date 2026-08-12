<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\POS;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository;

/**
 * Action responsável por buscar produtos no catálogo para o PDV (POS).
 */
class SearchProductAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $query = $queryParams['q'] ?? '';

        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(ProductRepository::class);

        $filterData = [
            'filter_name' => $query,
            'start'       => 0,
            'limit'       => 30,
        ];

        $productsRaw = $productRepo->getProducts($filterData);

        $imagePresenter = $this->getImagePresenter();
        $currency = $this->container->has('currency') ? $this->container->get('currency') : null;
        $config = $this->container->has('config') ? $this->container->get('config') : null;
        $currencyCode = $config ? $config->get('config_currency') : 'BRL';

        $products = [];
        foreach ($productsRaw as $p) {
            $productId = (int)($p['product_id'] ?? $p['id'] ?? 0);
            $price = (float)$p['price'];
            $special = !empty($p['special']) ? (float)$p['special'] : null;

            $priceFormatted = $currency ? $currency->format($price, $currencyCode) : 'R$ ' . number_format($price, 2, ',', '.');
            $specialFormatted = $special !== null ? ($currency ? $currency->format($special, $currencyCode) : 'R$ ' . number_format($special, 2, ',', '.')) : null;

            // Busca variações filhas do produto pai
            $variantsRaw = $productRepo->getProductVariants($productId);
            $variants = [];
            foreach ($variantsRaw as $variant) {
                if ($variant['status']) {
                    // Não mostrar variação cuja quantidade seja <= 0 e o stock status seja "esgotado" (5)
                    if ((int)$variant['quantity'] <= 0 && (int)$variant['stock_status_id'] === 5) {
                        continue;
                    }
                    $vPriceRaw = (float)($variant['price'] ?? 0);
                    if ($vPriceRaw <= 0.0) {
                        $vPriceRaw = $price;
                    }

                    $vPriceFormatted = $currency ? $currency->format($vPriceRaw, $currencyCode) : 'R$ ' . number_format($vPriceRaw, 2, ',', '.');

                    $variants[] = [
                        'product_id'      => (int)$variant['id'],
                        'name'            => $variant['variant'],
                        'sku'             => $variant['sku'],
                        'price'           => $vPriceRaw,
                        'price_formatted' => $vPriceFormatted,
                        'quantity'        => (int)$variant['quantity'],
                        'image'           => !empty($variant['image']) ? (strpos($variant['image'], 'image/') === 0 ? '/' . $variant['image'] : (strpos($variant['image'], '/image/') === 0 ? $variant['image'] : '/image/' . $variant['image'])) : '/image/no-image.png'
                    ];
                }
            }

            $products[] = [
                'product_id' => $productId,
                'name'       => $p['name'],
                'model'      => $p['model'] ?? '',
                'price'      => $price,
                'special'    => $special,
                'price_formatted' => $priceFormatted,
                'special_formatted' => $specialFormatted,
                'image'      => !empty($p['image']) ? (strpos($p['image'], 'image/') === 0 ? '/' . $p['image'] : (strpos($p['image'], '/image/') === 0 ? $p['image'] : '/image/' . $p['image'])) : '/image/no-image.png',
                'thumb'      => $imagePresenter->resize($p['image'] ?? '', 80, 80),
                'quantity'   => (int)($p['quantity'] ?? 0),
                'variants'   => $variants,
            ];
        }

        $response->getBody()->write(json_encode($products, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
