<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Alpha\Model\Domain\Repositories\ProductRepository;

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
        
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        
        // 1. Busca os dados do produto
        $stmt = $conn->prepare("
            SELECT p.*, pd.name, pd.description 
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.id = pd.product_id AND pd.language_id = ?
            WHERE p.id = ?
        ");
        $stmt->execute([$this->languageId, $productId]);
        $product = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$product) {
            $response->getBody()->write('Produto não encontrado.');
            return $response->withStatus(404);
        }

        // 2. Busca lista de fabricantes para o select
        $stmtManufacturers = $conn->query("SELECT id, name FROM `" . DB_PREFIX . "manufacturer` ORDER BY name ASC");
        $manufacturers = $stmtManufacturers->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Busca lista de status de estoque para o select
        $stmtStockStatuses = $conn->prepare("SELECT id, name FROM `" . DB_PREFIX . "stock_status` WHERE language_id = ? ORDER BY name ASC");
        $stmtStockStatuses->execute([$this->languageId]);
        $stockStatuses = $stmtStockStatuses->fetchAll(\PDO::FETCH_ASSOC);

        // 4. Busca as variações (produtos filhos) cadastradas
        $variants = $productRepo->getProductVariants($productId);

        $html = $this->getTemplate('admin/pages/products/edit.html.twig', [
            'title'          => 'Editar Produto | Painel Administrativo',
            'product'        => $product,
            'manufacturers'  => $manufacturers,
            'stock_statuses' => $stockStatuses,
            'variants'       => $variants
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
