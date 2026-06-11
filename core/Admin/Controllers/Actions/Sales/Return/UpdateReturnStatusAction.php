<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Return;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UpdateReturnStatusAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $returnId = (int)($args['id'] ?? 0);
        $postData = $request->getParsedBody();

        $returnStatusId = (int)($postData['return_status_id'] ?? 0);
        $returnActionId = (int)($postData['return_action_id'] ?? 0);
        $comment = (string)($postData['comment'] ?? '');
        $notify = isset($postData['notify']) && $postData['notify'] == '1' ? 1 : 0;

        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();

        // Verify return existence
        $stmtCheck = $conn->prepare("SELECT id FROM `" . DB_PREFIX . "product_return` WHERE id = ?");
        $stmtCheck->execute([$returnId]);
        $returnExists = $stmtCheck->fetchColumn();

        if (!$returnExists) {
            $response->getBody()->write("Devolução não encontrada.");
            return $response->withStatus(404);
        }

        // Update return status and action
        if ($returnStatusId > 0 || $returnActionId > 0) {
            $updateFields = [];
            $params = ['id' => $returnId];

            if ($returnStatusId > 0) {
                $updateFields[] = "return_status_id = :status_id";
                $params['status_id'] = $returnStatusId;
            }
            if ($returnActionId > 0) {
                $updateFields[] = "return_action_id = :action_id";
                $params['action_id'] = $returnActionId;
            }

            $updateFields[] = "date_modified = NOW()";

            $stmtUpdate = $conn->prepare("
                UPDATE `" . DB_PREFIX . "product_return`
                SET " . implode(", ", $updateFields) . "
                WHERE id = :id
            ");
            $stmtUpdate->execute($params);

            // Insert history log
            // Use returnStatusId if provided, else keep current status from the DB
            $historyStatusId = $returnStatusId;
            if ($historyStatusId <= 0) {
                $stmtCurrentStatus = $conn->prepare("SELECT return_status_id FROM `" . DB_PREFIX . "product_return` WHERE id = ?");
                $stmtCurrentStatus->execute([$returnId]);
                $historyStatusId = (int)$stmtCurrentStatus->fetchColumn();
            }

            $stmtHistory = $conn->prepare("
                INSERT INTO `" . DB_PREFIX . "return_history`
                SET return_id = :return_id,
                    return_status_id = :status_id,
                    comment = :comment,
                    notify = :notify,
                    date_added = NOW()
            ");
            $stmtHistory->execute([
                'return_id' => $returnId,
                'status_id' => $historyStatusId,
                'comment'   => $comment,
                'notify'    => $notify
            ]);
        }

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $redirectUrl = $routeContext->getRouteParser()->urlFor('admin.returns.show', ['id' => $returnId]);
        } catch (\Throwable $e) {
            $redirectUrl = '/LPDHED2dC7Gjrg2b/devolucoes/' . $returnId;
        }

        return $response->withHeader('Location', $redirectUrl . '?success=1')->withStatus(302);
    }
}
