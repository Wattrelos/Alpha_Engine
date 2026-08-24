<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Provider;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * SearchCatalogItemsAction - Endpoint JSON para pesquisa rápida de SKUs na ferramenta de Takeoff.
 */
class SearchCatalogItemsAction implements ActionInterface
{
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();
        $query = trim($params['q'] ?? '');
        $languageId = (int)$request->getAttribute('language_id', 2);

        if (empty($query)) {
            $response->getBody()->write(json_encode([]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $results = $this->productRepository->searchProducts([
            'filter_name' => $query,
            'language_id' => $languageId,
            'start' => 0,
            'limit' => 10
        ]);

        $formatted = array_map(function ($product) {
            return [
                'id' => $product['product_id'] ?? $product['id'],
                'name' => $product['name'] ?? '',
                'model' => $product['model'] ?? '',
                'price' => (float)($product['price'] ?? 0.0),
                'formatted_price' => 'R$ ' . number_format((float)($product['price'] ?? 0.0), 2, ',', '.')
            ];
        }, $results);

        $response->getBody()->write(json_encode($formatted));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
