<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class CreateProductAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $conn = ConnectionDB::getInstance()->getConnection();

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $name = trim($data['name'] ?? '');
            $model = trim($data['model'] ?? '');
            $price = (float)($data['price'] ?? 0.0);
            $quantity = (int)($data['quantity'] ?? 0);
            $status = isset($data['status']) ? (int)$data['status'] : 1;
            $description = $data['description'] ?? '';

            $ean = trim($data['ean'] ?? '');
            $stockStatusId = (int)($data['stock_status_id'] ?? 7); // Default to 'In Stock' (7)
            $manufacturerId = (int)($data['manufacturer_id'] ?? 0);
            $dbManufacturerId = $manufacturerId === 0 ? null : $manufacturerId;
            
            $dateAvailable = trim($data['date_available'] ?? '');
            if (empty($dateAvailable)) {
                $dateAvailable = date('Y-m-d');
            }

            $categoryIds = isset($data['product_category']) && is_array($data['product_category']) ? $data['product_category'] : [];

            if (empty($name)) {
                $response->getBody()->write('O nome do produto é obrigatório.');
                return $response->withStatus(400);
            }

            try {
                $conn->beginTransaction();

                // 1. Handle image upload
                $uploadedFiles = $request->getUploadedFiles();
                /** @var \Psr\Http\Message\UploadedFileInterface|null $imageFile */
                $imageFile = $uploadedFiles['image'] ?? null;
                $imagePath = '';

                if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                    $clientFilename = $imageFile->getClientFilename();
                    $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (in_array($extension, $allowedExtensions, true)) {
                        $safeFilename = sprintf('product_%d_%s.%s', time(), uniqid(), $extension);
                        $targetPathRel = 'image/product/' . $safeFilename;
                        $targetPathAbs = DIR_IMAGE . $targetPathRel;

                        $targetDir = dirname($targetPathAbs);
                        if (!is_dir($targetDir)) {
                            @mkdir($targetDir, 0755, true);
                        }

                        $imageFile->moveTo($targetPathAbs);
                        $imagePath = $targetPathRel;
                    }
                }

                // 2. Insert into product table
                $stmtProd = $conn->prepare("
                    INSERT INTO `" . DB_PREFIX . "product` (
                        `master_id`, `model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, 
                        `variant`, `override`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, 
                        `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, 
                        `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, 
                        `minimum`, `rating`, `sort_order`, `status`, `date_added`, `date_modified`, 
                        `ncm`, `cest`
                    ) VALUES (
                        0, ?, '', '', ?, '', '', '', '', 
                        '', '', ?, ?, ?, ?, 
                        1, ?, 0, 0, ?, 0.00000000, 
                        0, 0.00000000, 0.00000000, 0.00000000, 0, 1, 
                        1, 0, 0, ?, NOW(), NOW(), 
                        '', ''
                    )
                ");
                $stmtProd->execute([
                    $model, 
                    $ean, 
                    $quantity, 
                    $stockStatusId, 
                    $imagePath, 
                    $dbManufacturerId, 
                    $price, 
                    $dateAvailable, 
                    $status
                ]);
                $productId = (int)$conn->lastInsertId();

                // 3. Insert into product_description (for all languages)
                $stmtLangs = $conn->query("SELECT id FROM `" . DB_PREFIX . "language`");
                $languages = $stmtLangs->fetchAll(\PDO::FETCH_COLUMN);

                foreach ($languages as $langId) {
                    $stmtDesc = $conn->prepare("
                        INSERT INTO `" . DB_PREFIX . "product_description` (
                            `product_id`, `language_id`, `name`, `description`, `tag`, `meta_title`, `meta_description`, `meta_keyword`
                        ) VALUES (?, ?, ?, ?, '', ?, '', '')
                    ");
                    $stmtDesc->execute([
                        $productId, 
                        $langId, 
                        $name, 
                        $description, 
                        $name // meta_title
                    ]);
                }

                // 4. Insert into product_to_store (store_id = 1)
                $stmtStore = $conn->prepare("INSERT INTO `" . DB_PREFIX . "product_to_store` (`product_id`, `store_id`) VALUES (?, 1)");
                $stmtStore->execute([$productId]);

                // 5. Insert into product_to_category
                if (!empty($categoryIds)) {
                    $stmtCat = $conn->prepare("INSERT INTO `" . DB_PREFIX . "product_to_category` (`product_id`, `category_id`) VALUES (?, ?)");
                    foreach ($categoryIds as $categoryId) {
                        $stmtCat->execute([$productId, (int)$categoryId]);
                    }
                }

                $conn->commit();

                // Clear caches
                if ($this->container->has(\Alpha\Support\Cache\CacheStrategyInterface::class)) {
                    $cache = $this->container->get(\Alpha\Support\Cache\CacheStrategyInterface::class);
                    if ($cache) {
                        $cache->delete("product.{$productId}.{$this->languageId}.{$this->storeId}");
                    }
                }

            } catch (\Throwable $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                $response->getBody()->write('Erro ao criar produto: ' . $e->getMessage());
                return $response->withStatus(500);
            }

            // Redirect back to list
            return $response
                ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/produtos')
                ->withStatus(302);
        }

        // GET request - render the create view
        // 1. Busca lista de fabricantes para o select
        $stmtManufacturers = $conn->query("SELECT id, name FROM `" . DB_PREFIX . "manufacturer` ORDER BY name ASC");
        $manufacturers = $stmtManufacturers->fetchAll(\PDO::FETCH_ASSOC);

        // 2. Busca lista de status de estoque para o select
        $stmtStockStatuses = $conn->prepare("SELECT id, name FROM `" . DB_PREFIX . "stock_status` WHERE language_id = ? ORDER BY name ASC");
        $stmtStockStatuses->execute([$this->languageId]);
        $stockStatuses = $stmtStockStatuses->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Busca lista de categorias para o checkbox grid
        $stmtCategories = $conn->prepare("
            SELECT c.id, cd.name 
            FROM `" . DB_PREFIX . "category` c 
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON c.id = cd.category_id AND cd.language_id = ? 
            ORDER BY cd.name ASC
        ");
        $stmtCategories->execute([$this->languageId]);
        $categories = $stmtCategories->fetchAll(\PDO::FETCH_ASSOC);

        $html = $this->getTemplate('admin/pages/products/create.html.twig', [
            'title'          => 'Adicionar Produto | Painel Administrativo',
            'manufacturers'  => $manufacturers,
            'stock_statuses' => $stockStatuses,
            'categories'     => $categories
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
