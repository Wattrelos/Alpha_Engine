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
            $price = (float)$p['price'];
            $special = !empty($p['special']) ? (float)$p['special'] : null;

            $priceFormatted = $currency ? $currency->format($price, $currencyCode) : 'R$ ' . number_format($price, 2, ',', '.');
            $specialFormatted = $special !== null ? ($currency ? $currency->format($special, $currencyCode) : 'R$ ' . number_format($special, 2, ',', '.')) : null;

            $products[] = [
                'product_id' => (int)($p['product_id'] ?? $p['id'] ?? 0),
                'name'       => $p['name'],
                'model'      => $p['model'] ?? '',
                'price'      => $price,
                'special'    => $special,
                'price_formatted' => $priceFormatted,
                'special_formatted' => $specialFormatted,
                'image'      => !empty($p['image']) ? (strpos($p['image'], 'image/') === 0 ? '/' . $p['image'] : (strpos($p['image'], '/image/') === 0 ? $p['image'] : '/image/' . $p['image'])) : '/image/no_image.png',
                'thumb'      => $imagePresenter->resize($p['image'] ?? '', 80, 80),
                'quantity'   => (int)($p['quantity'] ?? 0),
            ];
        }

        $response->getBody()->write(json_encode($products, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
