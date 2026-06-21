<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository;

class ListProductsAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(ProductRepository::class);
        
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $limit = 15; // Exibir 15 produtos por página
        $start = ($page - 1) * $limit;

        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();

        // 1. Processa Filtros de Busca
        $where = [];
        $params = [];

        // Apenas produtos principais (master_id = 0)
        $where[] = "p.master_id = 0";

        // Filtro por Nome
        if (!empty($queryParams['filter_name'])) {
            $where[] = "pd.name LIKE ?";
            $params[] = "%" . $queryParams['filter_name'] . "%";
        }

        // Filtro por EAN
        if (!empty($queryParams['filter_ean'])) {
            $where[] = "p.ean = ?";
            $params[] = $queryParams['filter_ean'];
        }

        // Filtro por Categoria
        if (!empty($queryParams['filter_category_id'])) {
            $where[] = "p.id IN (SELECT product_id FROM `" . DB_PREFIX . "product_to_category` WHERE category_id = ?)";
            $params[] = (int)$queryParams['filter_category_id'];
        }

        // Filtro por Marca (Manufacturer)
        if (!empty($queryParams['filter_manufacturer_id'])) {
            $where[] = "p.manufacturer_id = ?";
            $params[] = (int)$queryParams['filter_manufacturer_id'];
        }

        // Filtro por Status
        if (isset($queryParams['filter_status']) && $queryParams['filter_status'] !== '') {
            $where[] = "p.status = ?";
            $params[] = (int)$queryParams['filter_status'];
        }

        $whereSql = '';
        if ($where) {
            $whereSql = "WHERE " . implode(" AND ", $where);
        }

        // 2. Query do total de produtos filtrados
        $countQuery = "
            SELECT COUNT(DISTINCT p.id) 
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.id = pd.product_id AND pd.language_id = ?
            $whereSql
        ";
        $stmtCount = $conn->prepare($countQuery);
        $stmtCount->execute(array_merge([$this->languageId], $params));
        $totalProducts = (int)$stmtCount->fetchColumn();

        // 3. Query dos dados dos produtos filtrados
        $dataQuery = "
            SELECT p.id, p.image, pd.name, p.model, p.price, p.quantity, p.status 
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.id = pd.product_id AND pd.language_id = ?
            $whereSql
            ORDER BY pd.name ASC
            LIMIT " . (int)$limit . " OFFSET " . (int)$start;
        $stmtData = $conn->prepare($dataQuery);
        $stmtData->execute(array_merge([$this->languageId], $params));
        $productsData = $stmtData->fetchAll(\PDO::FETCH_ASSOC);

        // 4. Carrega listas auxiliares para os filtros select
        $stmtCategories = $conn->prepare("
            SELECT c.id, cd.name 
            FROM `" . DB_PREFIX . "category` c 
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON c.id = cd.category_id AND cd.language_id = ? 
            ORDER BY cd.name ASC
        ");
        $stmtCategories->execute([$this->languageId]);
        $categories = $stmtCategories->fetchAll(\PDO::FETCH_ASSOC);

        $stmtManufacturers = $conn->query("SELECT id, name FROM `" . DB_PREFIX . "manufacturer` ORDER BY name ASC");
        $manufacturers = $stmtManufacturers->fetchAll(\PDO::FETCH_ASSOC);

        $imagePresenter = $this->getImagePresenter();
        $products = [];
        foreach ($productsData as $prod) {
            $products[] = [
                'product_id' => $prod['id'] ?? 0,
                'name'       => $prod['name'],
                'model'      => $prod['model'] ?? '',
                'price'      => 'R$ ' . number_format((float)($prod['price'] ?? 0), 2, ',', '.'),
                'quantity'   => $prod['quantity'] ?? 0,
                'status'     => $prod['status'] ? 'Ativo' : 'Inativo',
                'image'      => $imagePresenter->resize($prod['image'] ?? '', 40, 40, false)
            ];
        }

        // Reconstrói URL de paginação preservando os filtros ativos
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.product.list');
        } catch (\Throwable $e) {
            $baseUrl = '/LPDHED2dC7Gjrg2b/produtos';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/pages/products/list.html.twig', [
            'title'          => 'Produtos | Painel Administrativo',
            'products'       => $products,
            'total'          => $totalProducts,
            'limit'          => $limit,
            'current_page'   => $page,
            'url'            => $url,
            'categories'     => $categories,
            'manufacturers'  => $manufacturers,
            'filters'        => $queryParams, // envia filtros para pré-seleção no formulário
            'success'        => $queryParams['success'] ?? null,
            'error'          => $queryParams['error'] ?? null
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
