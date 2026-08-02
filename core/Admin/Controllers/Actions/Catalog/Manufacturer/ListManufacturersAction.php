<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class ListManufacturersAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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

        // Filtro de Loja
        $where[] = "m2s.store_id = ?";
        $params[] = $this->storeId;

        if (!empty($queryParams['filter_name'])) {
            $where[] = "m.name LIKE ?";
            $params[] = "%" . $queryParams['filter_name'] . "%";
        }

        $whereSql = '';
        if ($where) {
            $whereSql = "WHERE " . implode(" AND ", $where);
        }

        // 2. Query do total
        $countQuery = "
            SELECT COUNT(DISTINCT m.id) 
            FROM `" . DB_PREFIX . "manufacturer` m
            INNER JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON m.id = m2s.manufacturer_id
            $whereSql
        ";
        $stmtCount = $conn->prepare($countQuery);
        $stmtCount->execute($params);
        $totalManufacturers = (int)$stmtCount->fetchColumn();

        // 3. Query dos dados com paginação
        $dataQuery = "
            SELECT m.*
            FROM `" . DB_PREFIX . "manufacturer` m
            INNER JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON m.id = m2s.manufacturer_id
            $whereSql
            ORDER BY m.name ASC
            LIMIT " . (int)$limit . " OFFSET " . (int)$start;
        
        $stmtData = $conn->prepare($dataQuery);
        $stmtData->execute($params);
        $manufacturersData = $stmtData->fetchAll(\PDO::FETCH_ASSOC);

        $imagePresenter = $this->getImagePresenter();
        $manufacturers = [];
        foreach ($manufacturersData as $m) {
            $manufacturers[] = [
                'manufacturer_id' => $m['id'] ?? 0,
                'name'            => $m['name'] ?? '',
                'sort_order'      => $m['sort_order'] ?? 0,
                'image'           => $m['image'] ? $imagePresenter->resize($m['image'], 40, 40, false) : ''
            ];
        }

        // Reconstrói URL de paginação preservando os filtros ativos
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.manufacturer.list');
        } catch (\Throwable $e) {
            $baseUrl = (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/fabricantes';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/catalog/manufacturer/index.html.twig', [
            'title'         => 'Fabricantes | Painel Administrativo',
            'manufacturers' => $manufacturers,
            'total'         => $totalManufacturers,
            'limit'         => $limit,
            'current_page'  => $page,
            'url'           => $url,
            'filters'       => $queryParams
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
