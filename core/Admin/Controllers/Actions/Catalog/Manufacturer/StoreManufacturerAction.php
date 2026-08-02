<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class StoreManufacturerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $conn = ConnectionDB::getInstance()->getConnection();

        $data = $request->getParsedBody();

        $name = trim($data['name'] ?? '');
        $seoKeyword = trim($data['seo_keyword'] ?? '');
        $sortOrder = (int)($data['sort_order'] ?? 0);

        if (empty($name)) {
            $response->getBody()->write('O nome do fabricante é obrigatório.');
            return $response->withStatus(400);
        }

        try {
            $conn->beginTransaction();

            // 1. Processar upload de imagem
            $uploadedFiles = $request->getUploadedFiles();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $imageFile */
            $imageFile = $uploadedFiles['image'] ?? null;
            $imagePath = '';

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
                    $imagePath = $targetPathRel;
                }
            }

            // 2. Inserir na tabela manufacturer
            $stmtCat = $conn->prepare("INSERT INTO `" . DB_PREFIX . "manufacturer` (`name`, `image`, `sort_order`) VALUES (?, ?, ?)");
            $stmtCat->execute([$name, $imagePath, $sortOrder]);
            $manufacturerId = (int)$conn->lastInsertId();

            // 3. Inserir em manufacturer_to_store (store_id = $this->storeId)
            $stmtStore = $conn->prepare("INSERT INTO `" . DB_PREFIX . "manufacturer_to_store` (`manufacturer_id`, `store_id`) VALUES (?, ?)");
            $stmtStore->execute([$manufacturerId, $this->storeId]);

            // 4. Inserir SEO URL
            if (!empty($seoKeyword)) {
                $stmtSeo = $conn->prepare("INSERT INTO `" . DB_PREFIX . "seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES (?, ?, 'manufacturer_id', CAST(? AS CHAR), ?)");
                $stmtSeo->execute([$this->storeId, $this->languageId, $manufacturerId, $seoKeyword]);
            }

            $conn->commit();

            // Limpar caches
            $this->clearManufacturerCaches($manufacturerId);

        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $response->getBody()->write('Erro ao criar fabricante: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redireciona de volta para a lista
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
