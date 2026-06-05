<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class CreateCategoryAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $conn = ConnectionDB::getInstance()->getConnection();

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $name = trim($data['name'] ?? '');
            $metaTitle = trim($data['meta_title'] ?? '');
            $description = $data['description'] ?? '';
            $metaDescription = trim($data['meta_description'] ?? '');
            $metaKeyword = trim($data['meta_keyword'] ?? '');
            
            $parentId = (int)($data['parent_id'] ?? 0);
            $dbParentId = $parentId === 0 ? null : $parentId;
            $sortOrder = (int)($data['sort_order'] ?? 0);
            $status = isset($data['status']) ? (int)$data['status'] : 1;
            $seoKeyword = trim($data['seo_keyword'] ?? '');

            if (empty($name)) {
                $response->getBody()->write('O nome da categoria é obrigatório.');
                return $response->withStatus(400);
            }

            if (empty($metaTitle)) {
                $metaTitle = $name; // Fallback
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
                        $safeFilename = sprintf('category_%d_%s.%s', time(), uniqid(), $extension);
                        $targetPathRel = 'image/category/' . $safeFilename;
                        $targetPathAbs = DIR_IMAGE . $targetPathRel;

                        $targetDir = dirname($targetPathAbs);
                        if (!is_dir($targetDir)) {
                            @mkdir($targetDir, 0755, true);
                        }

                        $imageFile->moveTo($targetPathAbs);
                        $imagePath = $targetPathRel;
                    }
                }

                // 2. Insert into category table
                $stmtCat = $conn->prepare("INSERT INTO `" . DB_PREFIX . "category` (`image`, `parent_id`, `sort_order`, `status`) VALUES (?, ?, ?, ?)");
                $stmtCat->execute([$imagePath, $dbParentId, $sortOrder, $status]);
                $categoryId = (int)$conn->lastInsertId();

                // 3. Insert into category_description (for all languages)
                $stmtLangs = $conn->query("SELECT id FROM `" . DB_PREFIX . "language`");
                $languages = $stmtLangs->fetchAll(\PDO::FETCH_COLUMN);

                foreach ($languages as $langId) {
                    $stmtDesc = $conn->prepare("INSERT INTO `" . DB_PREFIX . "category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmtDesc->execute([$categoryId, $langId, $name, $description, $metaTitle, $metaDescription, $metaKeyword]);
                }

                // 4. Insert into category_to_store (store_id = 1)
                $stmtStore = $conn->prepare("INSERT INTO `" . DB_PREFIX . "category_to_store` (`category_id`, `store_id`) VALUES (?, 1)");
                $stmtStore->execute([$categoryId]);

                // 5. Build category paths
                $this->rebuildCategoryPaths($categoryId, $dbParentId, $conn);

                // 6. Insert SEO URL
                if (!empty($seoKeyword)) {
                    $stmtSeo = $conn->prepare("INSERT INTO `" . DB_PREFIX . "seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES (?, ?, 'category_id', ?, ?)");
                    $stmtSeo->execute([$this->storeId, $this->languageId, $categoryId, $seoKeyword]);
                }

                $conn->commit();

                // Clear caches
                $this->clearCategoryCaches($categoryId);

            } catch (\Throwable $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                $response->getBody()->write('Erro ao criar categoria: ' . $e->getMessage());
                return $response->withStatus(500);
            }

            // Redirect back to list
            return $response
                ->withHeader('Location', '/LPDHED2dC7Gjrg2b/categorias')
                ->withStatus(302);
        }

        // GET request - render the create view
        $stmtCategories = $conn->prepare("
            SELECT c.id, cd.name 
            FROM `" . DB_PREFIX . "category` c 
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON c.id = cd.category_id AND cd.language_id = ? 
            ORDER BY cd.name ASC
        ");
        $stmtCategories->execute([$this->languageId]);
        $categories = $stmtCategories->fetchAll(\PDO::FETCH_ASSOC);

        $html = $this->getTemplate('admin/pages/category/create.html.twig', [
            'title'      => 'Adicionar Categoria | Painel Administrativo',
            'categories' => $categories
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    private function rebuildCategoryPaths(int $categoryId, ?int $parentId, \PDO $conn): void
    {
        // Delete existing paths for this category
        $stmt = $conn->prepare("DELETE FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = ?");
        $stmt->execute([$categoryId]);

        // Fetch parent's paths
        $paths = [];
        if ($parentId !== null && $parentId > 0) {
            $stmt = $conn->prepare("SELECT `path_id`, `level` FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = ? ORDER BY `level` ASC");
            $stmt->execute([$parentId]);
            $paths = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $level = 0;
        foreach ($paths as $path) {
            $stmtIns = $conn->prepare("INSERT INTO `" . DB_PREFIX . "category_path` (`category_id`, `path_id`, `level`) VALUES (?, ?, ?)");
            $stmtIns->execute([$categoryId, $path['path_id'], $level]);
            $level++;
        }

        // Insert self-reference
        $stmtIns = $conn->prepare("INSERT INTO `" . DB_PREFIX . "category_path` (`category_id`, `path_id`, `level`) VALUES (?, ?, ?)");
        $stmtIns->execute([$categoryId, $categoryId, $level]);
    }

    private function clearCategoryCaches(int $categoryId): void
    {
        if ($this->container->has(\Alpha\Support\Cache\CacheStrategyInterface::class)) {
            $cache = $this->container->get(\Alpha\Support\Cache\CacheStrategyInterface::class);
            if ($cache) {
                // Clear category menu caches and single category cache
                $cache->delete("category_menu_html.s{$this->storeId}.l{$this->languageId}");
                $cache->delete("category_menu_tree.s{$this->storeId}.l{$this->languageId}");
                $cache->delete("category.{$categoryId}.{$this->languageId}.{$this->storeId}");
            }
        }
    }
}
