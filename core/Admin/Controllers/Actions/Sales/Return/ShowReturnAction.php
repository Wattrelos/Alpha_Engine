<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Return;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ReturnDictionaryRepository;

class ShowReturnAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $returnId = (int)($args['id'] ?? 0);
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();

        // 1. Fetch return details without customer_id restriction
        $stmt = $conn->prepare("
            SELECT r.*, 
                   rr.name AS reason_name,
                   ra.name AS action_name,
                   rs.name AS status_name
            FROM `" . DB_PREFIX . "product_return` r
            LEFT JOIN `" . DB_PREFIX . "return_reason` rr ON r.return_reason_id = rr.id AND rr.language_id = :lang_id1
            LEFT JOIN `" . DB_PREFIX . "return_action` ra ON r.return_action_id = ra.id AND ra.language_id = :lang_id2
            LEFT JOIN `" . DB_PREFIX . "return_status` rs ON r.return_status_id = rs.id AND rs.language_id = :lang_id3
            WHERE r.id = :id
        ");
        $stmt->execute([
            'lang_id1' => $this->languageId,
            'lang_id2' => $this->languageId,
            'lang_id3' => $this->languageId,
            'id' => $returnId
        ]);
        $returnInfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$returnInfo) {
            $response->getBody()->write("Devolução não encontrada.");
            return $response->withStatus(404);
        }

        // 2. Fetch histories
        $stmtHistory = $conn->prepare("
            SELECT rh.date_added, rs.name AS status, rh.comment, rh.notify
            FROM `" . DB_PREFIX . "return_history` rh
            LEFT JOIN `" . DB_PREFIX . "return_status` rs ON rh.return_status_id = rs.id AND rs.language_id = :lang_id
            WHERE rh.return_id = :return_id
            ORDER BY rh.date_added DESC
        ");
        $stmtHistory->execute([
            'lang_id' => $this->languageId,
            'return_id' => $returnId
        ]);
        $histories = $stmtHistory->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Load statuses and return actions for the dropdowns
        /** @var ReturnDictionaryRepository $dictRepo */
        $dictRepo = $this->getRepository(ReturnDictionaryRepository::class);
        $statuses = $dictRepo->getStatusesByLanguage($this->languageId);
        $actions = $dictRepo->getActionsByLanguage($this->languageId);

        $queryParams = $request->getQueryParams();
        $success = isset($queryParams['success']) && $queryParams['success'] == '1';

        // 4. Render template
        $html = $this->getTemplate('admin/sales/return/show.html.twig', [
            'title'      => 'Devolução #' . $returnId . ' | Painel Administrativo',
            'return'     => $returnInfo,
            'histories'  => $histories,
            'statuses'   => $statuses,
            'actions'    => $actions,
            'success'    => $success,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
