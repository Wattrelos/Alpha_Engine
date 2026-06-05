<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class ListCategoriesAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $limit = 15;
        $start = ($page - 1) * $limit;

        $conn = ConnectionDB::getInstance()->getConnection();

        // 1. Processa Filtros
        $where = [];
        $params = [];

        if (!empty($queryParams['filter_name'])) {
            $where[] = "cd.name LIKE ?";
            $params[] = "%" . $queryParams['filter_name'] . "%";
        }

        if (isset($queryParams['filter_status']) && $queryParams['filter_status'] !== '') {
            $where[] = "c.status = ?";
            $params[] = (int)$queryParams['filter_status'];
        }

        $whereSql = '';
        if ($where) {
            $whereSql = "WHERE " . implode(" AND ", $where);
        }

        // 2. Query do total
        $countQuery = "
            SELECT COUNT(DISTINCT c.id) 
            FROM `" . DB_PREFIX . "category` c
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON c.id = cd.category_id AND cd.language_id = ?
            $whereSql
        ";
        $stmtCount = $conn->prepare($countQuery);
        $stmtCount->execute(array_merge([$this->languageId], $params));
        $totalCategories = (int)$stmtCount->fetchColumn();

        // 3. Query dos dados com paginação
        $dataQuery = "
            SELECT c.id, c.image, cd.name, c.sort_order, c.status,
                   (SELECT name FROM `" . DB_PREFIX . "category_description` cd2 
                    WHERE cd2.category_id = c.parent_id AND cd2.language_id = cd.language_id LIMIT 1) AS parent_name
            FROM `" . DB_PREFIX . "category` c
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON c.id = cd.category_id AND cd.language_id = ?
            $whereSql
            ORDER BY cd.name ASC
            LIMIT " . (int)$limit . " OFFSET " . (int)$start;
        
        $stmtData = $conn->prepare($dataQuery);
        $stmtData->execute(array_merge([$this->languageId], $params));
        $categoriesData = $stmtData->fetchAll(\PDO::FETCH_ASSOC);

        $imagePresenter = $this->getImagePresenter();
        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[] = [
                'category_id' => $cat['id'] ?? 0,
                'name'        => $cat['name'] ?? '',
                'parent'      => $cat['parent_name'] ?? 'Raiz',
                'sort_order'  => $cat['sort_order'] ?? 0,
                'status'      => $cat['status'] ? 'Ativo' : 'Inativo',
                'image'       => $cat['image'] ? $imagePresenter->resize($cat['image'], 40, 40, false) : ''
            ];
        }

        // Reconstrói URL de paginação preservando os filtros ativos
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.category.list');
        } catch (\Throwable $e) {
            $baseUrl = '/LPDHED2dC7Gjrg2b/categorias';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/pages/category/list.html.twig', [
            'title'        => 'Categorias | Painel Administrativo',
            'categories'   => $categories,
            'total'        => $totalCategories,
            'limit'        => $limit,
            'current_page' => $page,
            'url'          => $url,
            'filters'      => $queryParams
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
