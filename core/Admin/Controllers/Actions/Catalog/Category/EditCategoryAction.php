<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class EditCategoryAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $categoryId = (int)($args['id'] ?? 0);

        if (!$categoryId) {
            $response->getBody()->write('ID da categoria não fornecido.');
            return $response->withStatus(400);
        }

        $conn = ConnectionDB::getInstance()->getConnection();

        // 1. Fetch category details
        $stmtCat = $conn->prepare("
            SELECT c.*, cd.name, cd.description, cd.meta_title, cd.meta_description, cd.meta_keyword,
                   (SELECT keyword FROM `" . DB_PREFIX . "seo_url` 
                    WHERE `key` = 'category_id' AND `value` = CAST(c.id AS CHAR) 
                      AND store_id = ? AND language_id = ? LIMIT 1) AS seo_keyword
            FROM `" . DB_PREFIX . "category` c
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON c.id = cd.category_id AND cd.language_id = ?
            WHERE c.id = ?
        ");
        $stmtCat->execute([$this->storeId, $this->languageId, $this->languageId, $categoryId]);
        $category = $stmtCat->fetch(\PDO::FETCH_ASSOC);

        if (!$category) {
            $response->getBody()->write('Categoria não encontrada.');
            return $response->withStatus(404);
        }

        // 2. Fetch list of available parent categories (excluding itself and its descendants to prevent circular reference)
        $stmtParents = $conn->prepare("
            SELECT c.id, cd.name 
            FROM `" . DB_PREFIX . "category` c 
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON c.id = cd.category_id AND cd.language_id = ? 
            WHERE c.id NOT IN (SELECT category_id FROM `" . DB_PREFIX . "category_path` WHERE path_id = ?)
            ORDER BY cd.name ASC
        ");
        $stmtParents->execute([$this->languageId, $categoryId]);
        $categories = $stmtParents->fetchAll(\PDO::FETCH_ASSOC);

        $html = $this->getTemplate('admin/pages/category/edit.html.twig', [
            'title'      => 'Editar Categoria | Painel Administrativo',
            'category'   => $category,
            'categories' => $categories
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
