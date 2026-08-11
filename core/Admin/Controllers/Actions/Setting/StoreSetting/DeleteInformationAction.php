<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\Setting\StoreSetting;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\InformationRepository;

class DeleteInformationAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'Identificador de página inválido.';
        } elseif (in_array($id, [1, 2, 3, 4], true)) {
            $_SESSION['error'] = 'Não é permitido excluir as páginas institucionais padrão do sistema.';
        } else {
            /** @var InformationRepository $infoRepo */
            $infoRepo = $this->getRepository(InformationRepository::class);
            $deleted = $infoRepo->deleteInformationPage($id);

            if ($deleted) {
                $_SESSION['success'] = 'Página institucional removida com sucesso.';
            } else {
                $_SESSION['error'] = 'Falha ao remover a página institucional solicitada.';
            }
        }

        $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';
        return $response
            ->withHeader('Location', $adminPath . '/configuracoes?tab=information')
            ->withStatus(302);
    }
}
