<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DeleteProductAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $productId = isset($args['id']) ? (int)$args['id'] : 0;
        $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';

        if ($productId <= 0) {
            return $response
                ->withHeader('Location', $adminPath . '/produtos?error=' . urlencode('ID do produto inválido.'))
                ->withStatus(302);
        }

        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(ProductRepository::class);

        // 1. Verify if the product exists
        $existing = $productRepo->getAdminProductForEdit($productId, $this->languageId);
        if (!$existing) {
            return $response
                ->withHeader('Location', $adminPath . '/produtos?error=' . urlencode('Produto não encontrado.'))
                ->withStatus(302);
        }

        try {
            $productRepo->deleteAdminProduct($productId, $this->storeId, $this->languageId);

            return $response
                ->withHeader('Location', $adminPath . '/produtos?success=' . urlencode('Produto excluído com sucesso.'))
                ->withStatus(302);
        } catch (\Throwable $e) {
            error_log("Erro ao deletar produto: " . $e->getMessage());
            return $response
                ->withHeader('Location', $adminPath . '/produtos?error=' . urlencode('Erro ao deletar produto: ' . $e->getMessage()))
                ->withStatus(302);
        }
    }
}

