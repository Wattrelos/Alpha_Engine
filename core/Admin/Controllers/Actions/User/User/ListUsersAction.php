<?php

namespace Alpha\Admin\Controllers\Actions\User\User;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\UserRepository;
use Alpha\Model\Domain\Repositories\UserGroupRepository;

class ListUsersAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getRepository(UserRepository::class);
        /** @var UserGroupRepository $userGroupRepo */
        $userGroupRepo = $this->getRepository(UserGroupRepository::class);

        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 15;

        $allUsers = $userRepo->findAll();
        $allGroups = $userGroupRepo->findAll();
        $groupMap = [];
        foreach ($allGroups as $grp) {
            $groupMap[$grp->getId()] = $grp->getName();
        }

        // Filtros em memória
        $filteredUsers = [];
        foreach ($allUsers as $u) {
            if (!empty($queryParams['filter_username']) && !str_contains(strtolower($u->getUsername()), strtolower($queryParams['filter_username']))) {
                continue;
            }
            if (!empty($queryParams['filter_name'])) {
                $fullName = $u->getFirstname() . ' ' . $u->getLastname();
                if (!str_contains(strtolower($fullName), strtolower($queryParams['filter_name']))) {
                    continue;
                }
            }
            if (!empty($queryParams['filter_user_group_id']) && (int)$u->getUserGroupId() !== (int)$queryParams['filter_user_group_id']) {
                continue;
            }
            if (isset($queryParams['filter_status']) && $queryParams['filter_status'] !== '' && (int)$u->isStatus() !== (int)$queryParams['filter_status']) {
                continue;
            }
            $filteredUsers[] = $u;
        }

        $totalUsers = count($filteredUsers);
        $offset = ($page - 1) * $limit;
        $pagedUsers = array_slice($filteredUsers, $offset, $limit);

        $imagePresenter = $this->getImagePresenter();
        $usersData = [];
        foreach ($pagedUsers as $user) {
            $usersData[] = [
                'user_id'     => $user->getId(),
                'username'    => $user->getUsername(),
                'name'        => trim($user->getFirstname() . ' ' . $user->getLastname()),
                'email'       => $user->getEmail(),
                'user_group'  => $groupMap[$user->getUserGroupId()] ?? 'Indefinido',
                'status'      => $user->isStatus(),
                'image'       => $imagePresenter->resize($user->getImage(), 40, 40, false),
                'date_added'  => $user->getDateAdded() ? date('d/m/Y H:i', strtotime($user->getDateAdded())) : '-'
            ];
        }

        $html = $this->getTemplate('admin/user/user_list.html.twig', [
            'title'        => 'Funcionários & Usuários | Painel Administrativo',
            'users'        => $usersData,
            'user_groups'  => $allGroups,
            'total'        => $totalUsers,
            'limit'        => $limit,
            'current_page' => $page,
            'filters'      => $queryParams,
            'success'      => $queryParams['success'] ?? null,
            'error'        => $queryParams['error'] ?? null
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
