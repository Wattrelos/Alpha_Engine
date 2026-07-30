<?php

namespace Alpha\Admin\Controllers\Actions\User\UserGroup;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\UserGroupRepository;
use Slim\Routing\RouteContext;

class DeleteUserGroupAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        /** @var UserGroupRepository $userGroupRepo */
        $userGroupRepo = $this->getRepository(UserGroupRepository::class);

        $getRedirectUrl = function (string $msgKey, string $msg) use ($request) {
            try {
                $routeContext = RouteContext::fromRequest($request);
                return $routeContext->getRouteParser()->urlFor('admin.user_group.list') . '?' . $msgKey . '=' . urlencode($msg);
            } catch (\Throwable $e) {
                return '/LPDHED2dC7Gjrg2b/papeis?' . $msgKey . '=' . urlencode($msg);
            }
        };

        if ($id === 1) {
            $url = $getRedirectUrl('error', 'Atenção: O grupo "Super Administrator" (ID 1) não pode ser excluído.');
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $userCount = $userGroupRepo->countUsersInGroup($id);
        if ($userCount > 0) {
            $url = $getRedirectUrl('error', "Não é possível excluir este papel pois existem {$userCount} funcionário(s) vinculado(s) a ele.");
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $userGroupRepo->delete($id);
        $url = $getRedirectUrl('success', 'Papel excluído com sucesso!');

        return $response->withHeader('Location', $url)->withStatus(302);
    }
}
