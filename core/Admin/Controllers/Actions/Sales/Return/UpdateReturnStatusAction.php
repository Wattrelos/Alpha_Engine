<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Return;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderReturnRepository;
use Alpha\Model\Domain\Repositories\ReturnDictionaryRepository;
use Alpha\Model\Domain\Repositories\ReturnHistoryRepository;
use Alpha\Model\Domain\Entities\ReturnHistory;
use Alpha\Model\DataAccessObject\UnitOfWork;
use Alpha\Model\DataAccessObject\ConcurrencyException;
use Alpha\Model\DataAccessObject\DataAccessObject;

class UpdateReturnStatusAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $returnId = (int)($args['id'] ?? 0);
        
        // Suporta payloads de formulário tradicional e chamadas JSON do AJAX
        $postData = $request->getParsedBody();
        if (empty($postData)) {
            $input = file_get_contents('php://input');
            $postData = json_decode($input, true) ?? [];
        }

        $returnStatusId = (int)($postData['return_status_id'] ?? $postData['status'] ?? 0);
        $returnActionId = (int)($postData['return_action_id'] ?? 0);
        $comment = (string)($postData['comment'] ?? '');
        $notify = isset($postData['notify']) && ($postData['notify'] == '1' || $postData['notify'] === true) ? 1 : 0;
        $version = isset($postData['version']) ? (int)$postData['version'] : null;

        /** @var OrderReturnRepository $returnRepo */
        $returnRepo = $this->getRepository(OrderReturnRepository::class);

        /** @var \Alpha\Model\Domain\Entities\OrderReturn|null $orderReturn */
        $orderReturn = $returnRepo->find($returnId);

        if (!$orderReturn) {
            if ($this->isJsonRequest($request)) {
                $response->getBody()->write(json_encode(['error' => 'RETURN_NOT_FOUND']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            $response->getBody()->write("Devolução não encontrada.");
            return $response->withStatus(404);
        }

        // Se uma versão específica foi fornecida pelo cliente, define na entidade para validação
        if ($version !== null) {
            $orderReturn->setVersion($version);
        }

        $uow = new UnitOfWork();

        try {
            $uow->transaction(function () use ($orderReturn, $returnStatusId, $returnActionId, $comment, $notify, $returnRepo) {
                // Atualiza os atributos da entidade em memória
                if ($returnStatusId > 0) {
                    $orderReturn->setReturnStatusId($returnStatusId);
                }
                if ($returnActionId > 0) {
                    $orderReturn->setReturnActionId($returnActionId);
                }
                $orderReturn->setDateModified(date('Y-m-d H:i:s'));

                // Salva a entidade OrderReturn no banco de dados (valida versão)
                $returnRepo->save($orderReturn);

                // Insere no histórico do RMA
                $historyStatusId = $returnStatusId > 0 ? $returnStatusId : $orderReturn->getReturnStatusId();

                $history = new ReturnHistory();
                $history->setReturnId($orderReturn->getId());
                $history->setReturnStatusId($historyStatusId);
                $history->setComment($comment);
                $history->setNotify($notify);
                $history->setDateAdded(date('Y-m-d H:i:s'));

                /** @var ReturnHistoryRepository $historyRepo */
                $historyRepo = $this->getRepository(ReturnHistoryRepository::class);
                $historyRepo->save($history);
            });

            // Resposta de sucesso
            if ($this->isJsonRequest($request)) {
                $response->getBody()->write(json_encode([
                    'success' => true,
                    'new_version' => $orderReturn->getVersion(),
                    'date_modified' => date('d/m/Y H:i', strtotime($orderReturn->getDateModified()))
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }

            try {
                $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
                $redirectUrl = $routeContext->getRouteParser()->urlFor('admin.returns.show', ['id' => $returnId]);
            } catch (\Throwable $e) {
                $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';
                $redirectUrl = $adminPath . '/devolucoes/' . $returnId;
            }

            return $response->withHeader('Location', $redirectUrl . '?success=1')->withStatus(302);

        } catch (ConcurrencyException $e) {
            // Falha de Concorrência Otimista (Registro alterado concorrentemente)
            // Limpa o Identity Map do DAO para forçar nova consulta no banco
            DataAccessObject::clearIdentityMap();
            
            /** @var \Alpha\Model\Domain\Entities\OrderReturn|null $freshOrderReturn */
            $freshOrderReturn = $returnRepo->find($returnId);

            // Carrega o nome do status atualizado via repositório
            /** @var ReturnDictionaryRepository $dictRepo */
            $dictRepo = $this->getRepository(ReturnDictionaryRepository::class);
            $statuses = $dictRepo->getStatusesByLanguage($this->languageId);
            $statusName = 'Desconhecido';
            foreach ($statuses as $st) {
                if (method_exists($st, 'getId') && $st->getId() === $freshOrderReturn->getReturnStatusId()) {
                    $statusName = method_exists($st, 'getName') ? $st->getName() : 'Desconhecido';
                    break;
                }
            }

            if ($this->isJsonRequest($request)) {
                $response->getBody()->write(json_encode([
                    'error' => 'DATA_STALE',
                    'current_data' => [
                        'version' => $freshOrderReturn->getVersion(),
                        'status_name' => $statusName,
                        'return_status_id' => $freshOrderReturn->getReturnStatusId(),
                        'return_action_id' => $freshOrderReturn->getReturnActionId(),
                        'date_modified' => date('d/m/Y H:i', strtotime($freshOrderReturn->getDateModified()))
                    ]
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }

            try {
                $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
                $redirectUrl = $routeContext->getRouteParser()->urlFor('admin.returns.show', ['id' => $returnId]);
            } catch (\Throwable $err) {
                $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';
                $redirectUrl = $adminPath . '/devolucoes/' . $returnId;
            }

            return $response->withHeader('Location', $redirectUrl . '?error=concurrency_conflict')->withStatus(302);
        }
    }

    private function isJsonRequest(Request $request): bool
    {
        $accept = $request->getHeaderLine('Accept');
        $contentType = $request->getHeaderLine('Content-Type');
        return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');
    }
}
