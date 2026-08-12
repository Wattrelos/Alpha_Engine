<?php

namespace Alpha\Admin\Controllers\Actions\User\UserGroup;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\UserGroupRepository;

class ListUserGroupsAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var UserGroupRepository $userGroupRepo */
        $userGroupRepo = $this->getRepository(UserGroupRepository::class);
        $groups = $userGroupRepo->findAll();

        $userGroups = [];
        foreach ($groups as $group) {
            $userGroups[] = [
                'user_group_id' => $group->getId(),
                'name'          => $group->getName(),
                'total_users'   => $userGroupRepo->countUsersInGroup($group->getId())
            ];
        }

        $queryParams = $request->getQueryParams();

        $html = $this->getTemplate('admin/user_group/user_group_list.html.twig', [
            'title'       => 'Papéis e Permissões | Painel Administrativo',
            'user_groups' => $userGroups,
            'success'     => $queryParams['success'] ?? null,
            'error'       => $queryParams['error'] ?? null
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
