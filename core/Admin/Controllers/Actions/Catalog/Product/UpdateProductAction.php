<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\DataAccessObject\ConnectionDB;

class UpdateProductAction extends BaseController implements ActionInterface
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
        
        $conn = ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare("SELECT id FROM `" . DB_PREFIX . "product` WHERE `id` = ?");
        $stmt->execute([$productId]);
        $productExists = $stmt->fetchColumn();

        if (!$productExists) {
            $response->getBody()->write('Produto não encontrado.');
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();

        $name = trim($data['name'] ?? '');
        $model = trim($data['model'] ?? '');
        $price = (float)($data['price'] ?? 0.0);
        $quantity = (int)($data['quantity'] ?? 0);
        $status = isset($data['status']) ? (int)$data['status'] : 1;
        $description = $data['description'] ?? '';
        
        $ean = trim($data['ean'] ?? '');
        $stockStatusId = (int)($data['stock_status_id'] ?? 0);
        $manufacturerId = (int)($data['manufacturer_id'] ?? 0);
        $dateAvailable = trim($data['date_available'] ?? '');
        if (empty($dateAvailable)) {
            $dateAvailable = date('Y-m-d');
        }

        if (empty($name)) {
            $response->getBody()->write('O nome do produto é obrigatório.');
            return $response->withStatus(400);
        }

        try {
            $conn = ConnectionDB::getInstance()->getConnection();

            // Fetch the current image path
            $stmtImage = $conn->prepare("SELECT image FROM `" . DB_PREFIX . "product` WHERE `id` = ?");
            $stmtImage->execute([$productId]);
            $currentImagePath = $stmtImage->fetchColumn() ?: '';

            $removeImage = isset($data['remove_image']) && $data['remove_image'] == '1';
            $newImagePath = $currentImagePath;

            if ($removeImage) {
                $newImagePath = '';
            }

            // Handle file upload
            $uploadedFiles = $request->getUploadedFiles();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $imageFile */
            $imageFile = $uploadedFiles['image'] ?? null;

            if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                $clientFilename = $imageFile->getClientFilename();
                $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($extension, $allowedExtensions, true)) {
                    // Generate a safe unique filename: product_[id]_[time].[ext]
                    $safeFilename = sprintf('product_%d_%d.%s', $productId, time(), $extension);
                    
                    // Construct target directory. Note: DIR_IMAGE is /var/www/html/agsonhos/public_html/
                    $targetPathRel = 'image/product/' . $safeFilename;
                    $targetPathAbs = DIR_IMAGE . $targetPathRel;

                    // Ensure target folder exists
                    $targetDir = dirname($targetPathAbs);
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0755, true);
                    }

                    $imageFile->moveTo($targetPathAbs);
                    $newImagePath = $targetPathRel;
                }
            }
            
            // 1. Atualiza dados na tabela principal product (incluindo imagem)
            $stmt = $conn->prepare("UPDATE `" . DB_PREFIX . "product` SET `model` = ?, `price` = ?, `quantity` = ?, `status` = ?, `ean` = ?, `stock_status_id` = ?, `manufacturer_id` = ?, `date_available` = ?, `image` = ?, `date_modified` = NOW() WHERE `id` = ?");
            $stmt->execute([$model, $price, $quantity, $status, $ean, $stockStatusId, $manufacturerId, $dateAvailable, $newImagePath, $productId]);

            // 2. Atualiza a descrição na tabela product_description para o idioma atual
            $stmtDesc = $conn->prepare("UPDATE `" . DB_PREFIX . "product_description` SET `name` = ?, `description` = ? WHERE `product_id` = ? AND `language_id` = ?");
            $stmtDesc->execute([$name, $description, $productId, $this->languageId]);

            // Limpa cache do repositório para evitar dados antigos
            if ($this->container->has(\Alpha\Support\Cache\CacheStrategyInterface::class)) {
                $cache = $this->container->get(\Alpha\Support\Cache\CacheStrategyInterface::class);
                if ($cache) {
                    $cache->delete("product.{$productId}.{$this->languageId}.{$this->storeId}");
                }
            }
        } catch (\Throwable $e) {
            $response->getBody()->write('Erro ao salvar produto: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redireciona de volta para a listagem
        return $response
            ->withHeader('Location', '/LPDHED2dC7Gjrg2b/produtos')
            ->withStatus(302);
    }
}
