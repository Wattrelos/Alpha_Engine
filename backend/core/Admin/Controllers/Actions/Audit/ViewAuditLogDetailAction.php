<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\Audit;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Services\Audit\AuditLoggerService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * ViewAuditLogDetailAction - Retorna os detalhes completos do payload de uma entrada de auditoria específica.
 */
class ViewAuditLogDetailAction extends BaseController implements ActionInterface
{
    private AuditLoggerService $auditLogger;

    public function __construct(\Psr\Container\ContainerInterface $container, ?AuditLoggerService $auditLogger = null)
    {
        parent::__construct($container);
        $this->auditLogger = $auditLogger ?? new AuditLoggerService();
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        $log = $this->auditLogger->getAuditLogById($id, $this->storeId);

        if (!$log) {
            $payload = json_encode(['error' => 'Registro de auditoria não encontrado.'], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $payload = json_encode([
            'success' => true,
            'data'    => $log
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
