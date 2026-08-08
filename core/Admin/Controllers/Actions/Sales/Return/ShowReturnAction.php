<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Return;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\OrderReturnRepository;
use Alpha\Model\Domain\Repositories\ReturnDictionaryRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ShowReturnAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $returnId = (int)($args['id'] ?? 0);

        /** @var OrderReturnRepository $returnRepo */
        $returnRepo = $this->getRepository(OrderReturnRepository::class);

        // 1. Busca os detalhes da devolução via repositório
        $returnInfo = $returnRepo->getAdminReturnDetails($returnId, $this->languageId);

        if (!$returnInfo) {
            $response->getBody()->write("Devolução não encontrada.");
            return $response->withStatus(404);
        }

        // 2. Busca histórico via repositório
        $histories = $returnRepo->getAdminReturnHistories($returnId, $this->languageId);

        // 3. Carrega status e ações para os dropdowns via repositório
        /** @var ReturnDictionaryRepository $dictRepo */
        $dictRepo = $this->getRepository(ReturnDictionaryRepository::class);
        $statuses = $dictRepo->getStatusesByLanguage($this->languageId);
        $actions = $dictRepo->getActionsByLanguage($this->languageId);

        $queryParams = $request->getQueryParams();
        $success = isset($queryParams['success']) && $queryParams['success'] == '1';
        $error = $queryParams['error'] ?? null;

        // 4. Renderiza o template
        $html = $this->getTemplate('admin/sales/return/show.html.twig', [
            'title'      => 'Devolução #' . $returnId . ' | Painel Administrativo',
            'return'     => $returnInfo,
            'histories'  => $histories,
            'statuses'   => $statuses,
            'actions'    => $actions,
            'success'    => $success,
            'error'      => $error,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

