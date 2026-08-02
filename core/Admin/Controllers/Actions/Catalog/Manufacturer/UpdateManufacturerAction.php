<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class UpdateManufacturerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $manufacturerId = (int)($args['id'] ?? 0);

        if (!$manufacturerId) {
            $response->getBody()->write('ID do fabricante não fornecido.');
            return $response->withStatus(400);
        }

        $conn = ConnectionDB::getInstance()->getConnection();

        // Check if manufacturer exists
        $stmtCheck = $conn->prepare("SELECT id, image FROM `" . DB_PREFIX . "manufacturer` WHERE id = ?");
        $stmtCheck->execute([$manufacturerId]);
        $existing = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

        if (!$existing) {
            $response->getBody()->write('Fabricante não encontrado.');
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();

        $name = trim($data['name'] ?? '');
        $seoKeyword = trim($data['seo_keyword'] ?? '');
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $removeImage = isset($data['remove_image']) && $data['remove_image'] == '1';

        if (empty($name)) {
            $response->getBody()->write('O nome do fabricante é obrigatório.');
            return $response->withStatus(400);
        }

        try {
            $conn->beginTransaction();

            // 1. Process image upload or removal
            $currentImagePath = $existing['image'] ?? '';
            $newImagePath = $currentImagePath;

            if ($removeImage) {
                $newImagePath = '';
            }

            $uploadedFiles = $request->getUploadedFiles();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $imageFile */
            $imageFile = $uploadedFiles['image'] ?? null;

            if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                $clientFilename = $imageFile->getClientFilename();
                $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($extension, $allowedExtensions, true)) {
                    $safeFilename = sprintf('manufacturer_%d_%s.%s', time(), uniqid(), $extension);
                    $targetPathRel = 'image/manufacturer/' . $safeFilename;
                    $targetPathAbs = DIR_IMAGE . $targetPathRel;

                    $targetDir = dirname($targetPathAbs);
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0755, true);
                    }

                    $imageFile->moveTo($targetPathAbs);
                    $newImagePath = $targetPathRel;
                }
            }

            // 2. Update manufacturer table
            $stmtUpd = $conn->prepare("UPDATE `" . DB_PREFIX . "manufacturer` SET `name` = ?, `image` = ?, `sort_order` = ? WHERE `id` = ?");
            $stmtUpd->execute([$name, $newImagePath, $sortOrder, $manufacturerId]);

            // 3. Update SEO URL
            $stmtSeoDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'manufacturer_id' AND `value` = CAST(? AS CHAR)");
            $stmtSeoDel->execute([$manufacturerId]);

            if (!empty($seoKeyword)) {
                $stmtSeo = $conn->prepare("INSERT INTO `" . DB_PREFIX . "seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES (?, ?, 'manufacturer_id', CAST(? AS CHAR), ?)");
                $stmtSeo->execute([$this->storeId, $this->languageId, $manufacturerId, $seoKeyword]);
            }

            $conn->commit();

            // Clear caches
            $this->clearManufacturerCaches($manufacturerId);

        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $response->getBody()->write('Erro ao salvar fabricante: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redirect back to list
        return $response
            ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/fabricantes')
            ->withStatus(302);
    }

    private function clearManufacturerCaches(int $manufacturerId): void
    {
        if ($this->container->has(\Alpha\Support\Cache\CacheStrategyInterface::class)) {
            $cache = $this->container->get(\Alpha\Support\Cache\CacheStrategyInterface::class);
            if ($cache) {
                $cache->delete("manufacturer.{$manufacturerId}.{$this->languageId}.{$this->storeId}");
            }
        }
    }
}
