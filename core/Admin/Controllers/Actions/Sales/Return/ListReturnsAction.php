<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Return;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ReturnDictionaryRepository;

class ListReturnsAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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

        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();

        $where = [];
        $params = [];

        // Filter by Return ID
        if (!empty($queryParams['filter_return_id'])) {
            $where[] = "r.id = ?";
            $params[] = (int)$queryParams['filter_return_id'];
        }

        // Filter by Order ID
        if (!empty($queryParams['filter_order_id'])) {
            $where[] = "r.order_id = ?";
            $params[] = (int)$queryParams['filter_order_id'];
        }

        // Filter by Customer
        if (!empty($queryParams['filter_customer'])) {
            $where[] = "CONCAT(r.firstname, ' ', r.lastname) LIKE ?";
            $params[] = "%" . $queryParams['filter_customer'] . "%";
        }

        // Filter by Status
        if (!empty($queryParams['filter_return_status_id'])) {
            $where[] = "r.return_status_id = ?";
            $params[] = (int)$queryParams['filter_return_status_id'];
        }

        // Filter by Date Added
        if (!empty($queryParams['filter_date_added'])) {
            $where[] = "DATE(r.date_added) = ?";
            $params[] = $queryParams['filter_date_added'];
        }

        $whereSql = '';
        if ($where) {
            $whereSql = "WHERE " . implode(" AND ", $where);
        }

        // Query Total Count
        $countQuery = "
            SELECT COUNT(r.id)
            FROM `" . DB_PREFIX . "product_return` r
            $whereSql
        ";
        $stmtCount = $conn->prepare($countQuery);
        $stmtCount->execute($params);
        $totalReturns = (int)$stmtCount->fetchColumn();

        // Query Data
        $dataQuery = "
            SELECT r.id, r.order_id, r.firstname, r.lastname, r.product, r.model, r.date_added, r.return_status_id, rs.name AS status_name
            FROM `" . DB_PREFIX . "product_return` r
            LEFT JOIN `" . DB_PREFIX . "return_status` rs ON r.return_status_id = rs.id AND rs.language_id = ?
            $whereSql
            ORDER BY r.id DESC
            LIMIT " . (int)$limit . " OFFSET " . (int)$start;
        
        $stmtData = $conn->prepare($dataQuery);
        $stmtData->execute(array_merge([$this->languageId], $params));
        $returnsData = $stmtData->fetchAll(\PDO::FETCH_ASSOC);

        $returns = [];
        foreach ($returnsData as $r) {
            $returns[] = [
                'return_id'   => $r['id'],
                'order_id'    => $r['order_id'],
                'customer'    => $r['firstname'] . ' ' . $r['lastname'],
                'product'     => $r['product'],
                'model'       => $r['model'],
                'status'      => $r['status_name'] ?? 'Pendente',
                'date_added'  => date('d/m/Y H:i', strtotime($r['date_added'])),
            ];
        }

        // Load statuses for the select filter
        /** @var ReturnDictionaryRepository $dictRepo */
        $dictRepo = $this->getRepository(ReturnDictionaryRepository::class);
        $statuses = $dictRepo->getStatusesByLanguage($this->languageId);

        // Pagination URL reconstruction
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.returns.index');
        } catch (\Throwable $e) {
            $baseUrl = (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/devolucoes';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/sales/return/index.html.twig', [
            'title'        => 'Devoluções | Painel Administrativo',
            'returns'      => $returns,
            'total'        => $totalReturns,
            'limit'        => $limit,
            'current_page' => $page,
            'url'          => $url,
            'statuses'     => $statuses,
            'filters'      => $queryParams,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
