<?php

namespace Alpha\Admin\Controllers\Actions\User\User;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\UserRepository;
use Slim\Routing\RouteContext;

class DeleteUserAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        /** @var UserRepository $userRepo */
        $userRepo = $this->getRepository(UserRepository::class);

        $getRedirectUrl = function (string $msgKey, string $msg) use ($request) {
            try {
                $routeContext = RouteContext::fromRequest($request);
                return $routeContext->getRouteParser()->urlFor('admin.user.list') . '?' . $msgKey . '=' . urlencode($msg);
            } catch (\Throwable $e) {
                $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';
                return $adminPath . '/usuarios?' . $msgKey . '=' . urlencode($msg);
            }
        };

        // Obter ID do usuário logado na sessão do admin
        $loggedAdminId = (int)($_SESSION['admin_logged_user']['user_id'] ?? ($_SESSION['user_id'] ?? 0));

        if ($id > 0 && $id === $loggedAdminId) {
            $url = $getRedirectUrl('error', 'Atenção: Você não pode excluir a sua própria conta logada.');
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $user = $userRepo->find($id);
        if (!$user) {
            $url = $getRedirectUrl('error', 'Funcionário não encontrado.');
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $userRepo->delete($id);
        $url = $getRedirectUrl('success', 'Funcionário excluído com sucesso!');

        return $response->withHeader('Location', $url)->withStatus(302);
    }
}
