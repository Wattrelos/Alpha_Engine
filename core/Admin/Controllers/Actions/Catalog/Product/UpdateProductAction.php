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
            $stmt->execute([$model, $price, $quantity, $status, $ean, $stockStatusId, $dbManufacturerId, $dateAvailable, $newImagePath, $productId]);

            // 2. Atualiza a descrição na tabela product_description para o idioma atual
            $stmtDesc = $conn->prepare("UPDATE `" . DB_PREFIX . "product_description` SET `name` = ?, `description` = ? WHERE `product_id` = ? AND `language_id` = ?");
            $stmtDesc->execute([$name, $description, $productId, $this->languageId]);

            // 3. Processa Variações (Produtos Filhos)
            if (isset($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $index => $v) {
                    $vId = (int)($v['id'] ?? 0);
                    $vName = trim($v['name'] ?? '');
                    $vSku = trim($v['sku'] ?? '');
                    $vPrice = (float)($v['price'] ?? 0.0);
                    $vQuantity = (int)($v['quantity'] ?? 0);
                    $vStatus = isset($v['status']) ? (int)$v['status'] : 1;
                    $vDelete = isset($v['delete']) && $v['delete'] == '1';

                    if (empty($vName)) {
                        continue; // Variação sem nome é ignorada
                    }

                    if ($vId > 0) {
                        if ($vDelete) {
                            // Deleta a variação
                            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product` WHERE `id` = ? AND `master_id` = ?")->execute([$vId, $productId]);
                            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_description` WHERE `product_id` = ?")->execute([$vId]);
                            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE `product_id` = ?")->execute([$vId]);
                            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = ?")->execute([$vId]);
                        } else {
                            // Fetch existing variation image path
                            $stmtVImage = $conn->prepare("SELECT image FROM `" . DB_PREFIX . "product` WHERE `id` = ? AND `master_id` = ?");
                            $stmtVImage->execute([$vId, $productId]);
                            $vCurrentImagePath = $stmtVImage->fetchColumn() ?: '';

                            $vRemoveImage = isset($v['remove_image']) && $v['remove_image'] == '1';
                            $vNewImagePath = $vCurrentImagePath;

                            if ($vRemoveImage) {
                                $vNewImagePath = '';
                            }

                            // Process uploaded image for this variation
                            $vImageFile = $uploadedFiles["variant_image_{$index}"] ?? null;
                            if ($vImageFile && $vImageFile->getError() === UPLOAD_ERR_OK) {
                                $clientFilename = $vImageFile->getClientFilename();
                                $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
                                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                                if (in_array($extension, $allowedExtensions, true)) {
                                    $safeFilename = sprintf('product_var_%d_%d.%s', $vId, time(), $extension);
                                    $targetPathRel = 'image/product/' . $safeFilename;
                                    $targetPathAbs = DIR_IMAGE . $targetPathRel;

                                    $targetDir = dirname($targetPathAbs);
                                    if (!is_dir($targetDir)) {
                                        @mkdir($targetDir, 0755, true);
                                    }

                                    $vImageFile->moveTo($targetPathAbs);
                                    $vNewImagePath = $targetPathRel;
                                }
                            }

                            // Atualiza a variação
                            $conn->prepare("
                                UPDATE `" . DB_PREFIX . "product` 
                                SET `sku` = ?, `price` = ?, `quantity` = ?, `status` = ?, `variant` = ?, `model` = ?, `stock_status_id` = ?, `manufacturer_id` = ?, `date_available` = ?, `image` = ?, `date_modified` = NOW() 
                                WHERE `id` = ? AND `master_id` = ?
                            ")->execute([
                                $vSku, $vPrice, $vQuantity, $vStatus, $vName, $model . '-' . $vSku, $stockStatusId, $dbManufacturerId, $dateAvailable, $vNewImagePath, $vId, $productId
                            ]);

                            // Atualiza a descrição da variação (Nome do pai + nome da variação)
                            $vFullName = $name . ' - ' . $vName;
                            $conn->prepare("
                                UPDATE `" . DB_PREFIX . "product_description` 
                                SET `name` = ?, `description` = ? 
                                WHERE `product_id` = ?
                            ")->execute([
                                $vFullName, $description, $vId
                            ]);
                        }
                    } else if (!$vDelete) {
                        // Process uploaded image for new variation
                        $vNewImagePath = '';
                        $vImageFile = $uploadedFiles["variant_image_{$index}"] ?? null;
                        if ($vImageFile && $vImageFile->getError() === UPLOAD_ERR_OK) {
                            $clientFilename = $vImageFile->getClientFilename();
                            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                            if (in_array($extension, $allowedExtensions, true)) {
                                $safeFilename = sprintf('product_var_new_%d_%s.%s', $productId, uniqid(), $extension);
                                $targetPathRel = 'image/product/' . $safeFilename;
                                $targetPathAbs = DIR_IMAGE . $targetPathRel;

                                $targetDir = dirname($targetPathAbs);
                                if (!is_dir($targetDir)) {
                                    @mkdir($targetDir, 0755, true);
                                }

                                $vImageFile->moveTo($targetPathAbs);
                                $vNewImagePath = $targetPathRel;
                            }
                        }

                        // Cria uma nova variação
                        $stmtInsVariant = $conn->prepare("
                            INSERT INTO `" . DB_PREFIX . "product` (
                                `master_id`, `model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, 
                                `variant`, `override`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, 
                                `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, 
                                `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, 
                                `minimum`, `rating`, `sort_order`, `status`, `date_added`, `date_modified`, 
                                `ncm`, `cest`
                            ) VALUES (
                                ?, ?, ?, '', '', '', '', '', '', 
                                ?, '', ?, ?, ?, ?, 
                                1, ?, 0, 0, ?, 0.00000000, 
                                0, 0.00000000, 0.00000000, 0.00000000, 0, 1, 
                                1, 0, 0, ?, NOW(), NOW(), 
                                '', ''
                            )
                        ");
                        $stmtInsVariant->execute([
                            $productId, $model . '-' . $vSku, $vSku, $vName, $vQuantity, $stockStatusId, $vNewImagePath, $dbManufacturerId, $vPrice, $dateAvailable, $vStatus
                        ]);
                        $newVariantId = (int)$conn->lastInsertId();

                        // Insere descrição da variação para todos os idiomas
                        $stmtLangs = $conn->query("SELECT id FROM `" . DB_PREFIX . "language`");
                        $languages = $stmtLangs->fetchAll(\PDO::FETCH_COLUMN);

                        $vFullName = $name . ' - ' . $vName;
                        foreach ($languages as $langId) {
                            $conn->prepare("
                                INSERT INTO `" . DB_PREFIX . "product_description` (
                                    `product_id`, `language_id`, `name`, `description`, `tag`, `meta_title`, `meta_description`, `meta_keyword`
                                ) VALUES (?, ?, ?, ?, '', ?, '', '')
                            ")->execute([
                                $newVariantId, $langId, $vFullName, $description, $vFullName
                            ]);
                        }

                        // Vincula à loja (store_id = 1)
                        $conn->prepare("INSERT INTO `" . DB_PREFIX . "product_to_store` (`product_id`, `store_id`) VALUES (?, 1)")->execute([$newVariantId]);
                    }
                }
            }

            // 4. Edição em Lote: Propagar em lote as informações comuns do pai para os filhos
            $conn->prepare("
                UPDATE `" . DB_PREFIX . "product` 
                SET `manufacturer_id` = ?, `stock_status_id` = ?, `date_available` = ?, `date_modified` = NOW() 
                WHERE `master_id` = ?
            ")->execute([$dbManufacturerId, $stockStatusId, $dateAvailable, $productId]);

            // Atualizar categorias do produto pai
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = ?")->execute([$productId]);
            if (!empty($categoryIds)) {
                $stmtCat = $conn->prepare("INSERT INTO `" . DB_PREFIX . "product_to_category` (`product_id`, `category_id`) VALUES (?, ?)");
                foreach ($categoryIds as $categoryId) {
                    $stmtCat->execute([$productId, (int)$categoryId]);
                }
            }

            // Sincronizar categorias do pai para as variações
            $conn->prepare("
                DELETE FROM `" . DB_PREFIX . "product_to_category` 
                WHERE `product_id` IN (SELECT `id` FROM `" . DB_PREFIX . "product` WHERE `master_id` = ?)
            ")->execute([$productId]);

            $conn->prepare("
                INSERT INTO `" . DB_PREFIX . "product_to_category` (`product_id`, `category_id`)
                SELECT p.id, pc.category_id 
                FROM `" . DB_PREFIX . "product` p
                JOIN `" . DB_PREFIX . "product_to_category` pc ON pc.product_id = ?
                WHERE p.master_id = ?
            ")->execute([$productId, $productId]);

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
