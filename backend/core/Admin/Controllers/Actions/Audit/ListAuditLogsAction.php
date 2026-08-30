<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\Audit;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Services\Audit\AuditLoggerService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;

/**
 * ListAuditLogsAction - Gerencia a listagem e exibição analítica dos logs de auditoria e visitantes.
 */
class ListAuditLogsAction extends BaseController implements ActionInterface
{
    private AuditLoggerService $auditLogger;

    public function __construct(\Psr\Container\ContainerInterface $container, ?AuditLoggerService $auditLogger = null)
    {
        parent::__construct($container);
        $this->auditLogger = $auditLogger ?? new AuditLoggerService();
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $page = max(1, (int)($queryParams['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $search = !empty($queryParams['search']) ? trim((string)$queryParams['search']) : null;
        $event = !empty($queryParams['event']) ? trim((string)$queryParams['event']) : null;
        $startDate = !empty($queryParams['start_date']) ? trim((string)$queryParams['start_date']) : null;
        $endDate = !empty($queryParams['end_date']) ? trim((string)$queryParams['end_date']) : null;

        // 1. Busca estatísticas gerais para os cards
        $stats = $this->auditLogger->getAuditStats($this->storeId);

        // 2. Busca logs filtrados e total
        $logs = $this->auditLogger->getFilteredAuditLogs(
            $this->storeId,
            $limit,
            $offset,
            $search,
            $event,
            $startDate,
            $endDate
        );
        $totalLogs = $this->auditLogger->getTotalAuditLogsCount(
            $this->storeId,
            $search,
            $event,
            $startDate,
            $endDate
        );

        $totalPages = (int)ceil($totalLogs / $limit);

        // 3. Monta URL de paginação preservando filtros
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.audit.list');
        } catch (\Throwable $e) {
            $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';
            $baseUrl = $adminPath . '/auditoria';
        }

        $paginationBaseUrl = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page=';

        $html = $this->getTemplate('admin/pages/audit/index.html.twig', [
            'title'              => 'Auditoria & Logs de Acesso | AgSonhos Admin',
            'logs'               => $logs,
            'stats'              => $stats,
            'total'              => $totalLogs,
            'current_page'       => $page,
            'total_pages'        => $totalPages,
            'limit'              => $limit,
            'pagination_url'     => $paginationBaseUrl,
            'filters'            => [
                'search'     => $search ?? '',
                'event'      => $event ?? '',
                'start_date' => $startDate ?? '',
                'end_date'   => $endDate ?? '',
            ],
            'success'            => $queryParams['success'] ?? null,
            'error'              => $queryParams['error'] ?? null,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
