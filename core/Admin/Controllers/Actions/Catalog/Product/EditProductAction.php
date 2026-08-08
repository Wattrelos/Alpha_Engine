<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EditProductAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $productId = (int)($args['id'] ?? 0);

        if (!$productId) {
            $response->getBody()->write('ID do produto não fornecido.');
            return $response->withStatus(400);
        }

        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(ProductRepository::class);
        /** @var CategoryRepository $categoryRepo */
        $categoryRepo = $this->getRepository(CategoryRepository::class);
        /** @var ManufacturerRepository $manufacturerRepo */
        $manufacturerRepo = $this->getRepository(ManufacturerRepository::class);

        // 1. Busca os dados do produto via repositório
        $product = $productRepo->getAdminProductForEdit($productId, $this->languageId);

        if (!$product) {
            $response->getBody()->write('Produto não encontrado.');
            return $response->withStatus(404);
        }

        // 2. Busca lista de fabricantes para o select
        $manufacturers = $manufacturerRepo->getManufacturers();

        // 3. Busca lista de status de estoque para o select
        $stockStatuses = $productRepo->getStockStatuses($this->languageId);

        // 4. Busca lista de todas as categorias
        $categories = $categoryRepo->getCategoriesForSelect($this->languageId);

        // 5. Busca categorias atualmente vinculadas ao produto
        $productCategories = $productRepo->getAdminProductCategoryIds($productId);

        // 6. Busca as variações (produtos filhos) cadastradas
        $variants = $productRepo->getProductVariants($productId);

        $html = $this->getTemplate('admin/pages/products/edit.html.twig', [
            'title'              => 'Editar Produto | Painel Administrativo',
            'product'            => $product,
            'manufacturers'      => $manufacturers,
            'stock_statuses'     => $stockStatuses,
            'categories'         => $categories,
            'product_categories' => $productCategories,
            'variants'           => $variants
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}

