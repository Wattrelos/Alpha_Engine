<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class UpdateCategoryAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $categoryId = (int)($args['id'] ?? 0);

        if (!$categoryId) {
            $response->getBody()->write('ID da categoria não fornecido.');
            return $response->withStatus(400);
        }

        $conn = ConnectionDB::getInstance()->getConnection();

        // Check if category exists
        $stmtCheck = $conn->prepare("SELECT id, image, parent_id FROM `" . DB_PREFIX . "category` WHERE id = ?");
        $stmtCheck->execute([$categoryId]);
        $existing = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

        if (!$existing) {
            $response->getBody()->write('Categoria não encontrada.');
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();

        $name = trim($data['name'] ?? '');
        $metaTitle = trim($data['meta_title'] ?? '');
        $description = $data['description'] ?? '';
        $metaDescription = trim($data['meta_description'] ?? '');
        $metaKeyword = trim($data['meta_keyword'] ?? '');
        
        $parentId = (int)($data['parent_id'] ?? 0);
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $status = isset($data['status']) ? (int)$data['status'] : 1;
        $seoKeyword = trim($data['seo_keyword'] ?? '');

        if (empty($name)) {
            $response->getBody()->write('O nome da categoria é obrigatório.');
            return $response->withStatus(400);
        }

        if (empty($metaTitle)) {
            $metaTitle = $name;
        }

        try {
            $conn->beginTransaction();

            // 1. Process image upload or removal
            $currentImagePath = $existing['image'] ?? '';
            $removeImage = isset($data['remove_image']) && $data['remove_image'] == '1';
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
                    $safeFilename = sprintf('category_%d_%s.%s', time(), uniqid(), $extension);
                    $targetPathRel = 'image/category/' . $safeFilename;
                    $targetPathAbs = DIR_IMAGE . $targetPathRel;

                    $targetDir = dirname($targetPathAbs);
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0755, true);
                    }

                    $imageFile->moveTo($targetPathAbs);
                    $newImagePath = $targetPathRel;
                }
            }

            // 2. Update category table
            $stmtUpd = $conn->prepare("UPDATE `" . DB_PREFIX . "category` SET `image` = ?, `parent_id` = ?, `sort_order` = ?, `status` = ? WHERE `id` = ?");
            $stmtUpd->execute([$newImagePath, $parentId, $sortOrder, $status, $categoryId]);

            // 3. Update category_description (insert if missing for other languages to ensure integrity)
            $stmtLangs = $conn->query("SELECT id FROM `" . DB_PREFIX . "language`");
            $languages = $stmtLangs->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($languages as $langId) {
                $stmtCheckDesc = $conn->prepare("SELECT COUNT(*) FROM `" . DB_PREFIX . "category_description` WHERE category_id = ? AND language_id = ?");
                $stmtCheckDesc->execute([$categoryId, $langId]);
                $exists = (bool)$stmtCheckDesc->fetchColumn();

                if ($exists) {
                    $stmtDesc = $conn->prepare("UPDATE `" . DB_PREFIX . "category_description` SET `name` = ?, `description` = ?, `meta_title` = ?, `meta_description` = ?, `meta_keyword` = ? WHERE `category_id` = ? AND `language_id` = ?");
                    $stmtDesc->execute([$name, $description, $metaTitle, $metaDescription, $metaKeyword, $categoryId, $langId]);
                } else {
                    $stmtDesc = $conn->prepare("INSERT INTO `" . DB_PREFIX . "category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmtDesc->execute([$categoryId, $langId, $name, $description, $metaTitle, $metaDescription, $metaKeyword]);
                }
            }

            // 4. Rebuild paths if parent changed
            $oldParentId = (int)($existing['parent_id'] ?? 0);
            if ($oldParentId !== $parentId) {
                $this->rebuildCategoryPaths($categoryId, $parentId, $conn);
            }

            // 5. Update SEO URL
            $stmtSeoDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'category_id' AND `value` = CAST(? AS CHAR)");
            $stmtSeoDel->execute([$categoryId]);

            if (!empty($seoKeyword)) {
                $stmtSeo = $conn->prepare("INSERT INTO `" . DB_PREFIX . "seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES (?, ?, 'category_id', CAST(? AS CHAR), ?)");
                $stmtSeo->execute([$this->storeId, $this->languageId, $categoryId, $seoKeyword]);
            }

            $conn->commit();

            // Clear caches
            $this->clearCategoryCaches($categoryId);

        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $response->getBody()->write('Erro ao salvar categoria: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redirect back to list
        return $response
            ->withHeader('Location', '/LPDHED2dC7Gjrg2b/categorias')
            ->withStatus(302);
    }

    private function rebuildCategoryPaths(int $categoryId, int $parentId, \PDO $conn): void
    {
        // Delete existing paths for this category
        $stmt = $conn->prepare("DELETE FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = ?");
        $stmt->execute([$categoryId]);

        // Fetch parent's paths
        $stmt = $conn->prepare("SELECT `path_id`, `level` FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = ? ORDER BY `level` ASC");
        $stmt->execute([$parentId]);
        $paths = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $level = 0;
        foreach ($paths as $path) {
            $stmtIns = $conn->prepare("INSERT INTO `" . DB_PREFIX . "category_path` (`category_id`, `path_id`, `level`) VALUES (?, ?, ?)");
            $stmtIns->execute([$categoryId, $path['path_id'], $level]);
            $level++;
        }

        // Insert self-reference
        $stmtIns = $conn->prepare("INSERT INTO `" . DB_PREFIX . "category_path` (`category_id`, `path_id`, `level`) VALUES (?, ?, ?)");
        $stmtIns->execute([$categoryId, $categoryId, $level]);

        // Recursive rebuild for all direct subcategories of the updated category
        $stmtSubs = $conn->prepare("SELECT `id` FROM `" . DB_PREFIX . "category` WHERE `parent_id` = ?");
        $stmtSubs->execute([$categoryId]);
        $subs = $stmtSubs->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($subs as $subId) {
            $this->rebuildCategoryPaths((int)$subId, $categoryId, $conn);
        }
    }

    private function clearCategoryCaches(int $categoryId): void
    {
        if ($this->container->has(\Alpha\Support\Cache\CacheStrategyInterface::class)) {
            $cache = $this->container->get(\Alpha\Support\Cache\CacheStrategyInterface::class);
            if ($cache) {
                $cache->delete("category_menu_html.s{$this->storeId}.l{$this->languageId}");
                $cache->delete("category_menu_tree.s{$this->storeId}.l{$this->languageId}");
                $cache->delete("category.{$categoryId}.{$this->languageId}.{$this->storeId}");
            }
        }
    }
}
