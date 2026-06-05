<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class DeleteCategoryAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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
        $stmtCheck = $conn->prepare("SELECT id FROM `" . DB_PREFIX . "category` WHERE id = ?");
        $stmtCheck->execute([$categoryId]);
        $exists = (bool)$stmtCheck->fetchColumn();

        if (!$exists) {
            $response->getBody()->write('Categoria não encontrada.');
            return $response->withStatus(404);
        }

        try {
            $conn->beginTransaction();

            // 1. Fetch direct subcategories
            $stmtSubs = $conn->prepare("SELECT id FROM `" . DB_PREFIX . "category` WHERE parent_id = ?");
            $stmtSubs->execute([$categoryId]);
            $subIds = $stmtSubs->fetchAll(\PDO::FETCH_COLUMN);

            // 2. Set subcategories parent to 0 (root level)
            $stmtUpdateSubs = $conn->prepare("UPDATE `" . DB_PREFIX . "category` SET parent_id = 0 WHERE parent_id = ?");
            $stmtUpdateSubs->execute([$categoryId]);

            // 3. Rebuild paths for subcategories
            foreach ($subIds as $subId) {
                $this->rebuildCategoryPaths((int)$subId, 0, $conn);
            }

            // 4. Delete category path records
            $stmtPathDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = ? OR path_id = ?");
            $stmtPathDel->execute([$categoryId, $categoryId]);

            // 5. Delete category description records
            $stmtDescDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "category_description` WHERE category_id = ?");
            $stmtDescDel->execute([$categoryId]);

            // 6. Delete category to store records
            $stmtStoreDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE category_id = ?");
            $stmtStoreDel->execute([$categoryId]);

            // 7. Delete SEO URL keyword
            $stmtSeoDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'category_id' AND `value` = CAST(? AS CHAR)");
            $stmtSeoDel->execute([$categoryId]);

            // 8. Delete main category record
            $stmtCatDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "category` WHERE id = ?");
            $stmtCatDel->execute([$categoryId]);

            $conn->commit();

            // Clear caches
            $this->clearCategoryCaches($categoryId);

        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $response->getBody()->write('Erro ao excluir categoria: ' . $e->getMessage());
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
