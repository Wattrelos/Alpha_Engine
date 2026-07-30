<?php

namespace Alpha\Admin\Controllers\Actions\User\UserGroup;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\UserGroupRepository;
use Alpha\Model\Domain\Entities\UserGroup;
use Slim\Routing\RouteContext;

class EditUserGroupAction extends BaseController implements ActionInterface
{
    private array $modules = [
        'dashboard'             => 'Dashboard',
        'catalog/product'       => 'Produtos',
        'catalog/category'      => 'Categorias',
        'catalog/manufacturer'  => 'Fabricantes',
        'procurement/supplier'  => 'Fornecedores',
        'customer/customer'     => 'Clientes',
        'sales/order'           => 'Pedidos (Vendas)',
        'sales/return'          => 'Devoluções (Vendas)',
        'setting/store_setting' => 'Configurações da Loja',
        'pos/sales_rep'         => 'PDV - Vendedor',
        'pos/cashier'           => 'PDV - Caixa',
        'user/user'             => 'Gestão de Funcionários',
        'user/user_group'        => 'Gestão de Papéis & Permissões',
    ];

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        /** @var UserGroupRepository $userGroupRepo */
        $userGroupRepo = $this->getRepository(UserGroupRepository::class);
        /** @var UserGroup|null $group */
        $group = $userGroupRepo->find($id);

        if (!$group) {
            try {
                $routeContext = RouteContext::fromRequest($request);
                $url = $routeContext->getRouteParser()->urlFor('admin.user_group.list') . '?error=' . urlencode('Papel não encontrado.');
            } catch (\Throwable $e) {
                $url = '/admin/papeis?error=' . urlencode('Papel não encontrado.');
            }
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $error = null;

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody() ?? [];
            $name = trim($parsedBody['name'] ?? '');
            $accessPermissions = $parsedBody['permission']['access'] ?? [];
            $modifyPermissions = $parsedBody['permission']['modify'] ?? [];

            if (empty($name)) {
                $error = 'O nome do papel é obrigatório.';
            } else {
                $permissionJson = json_encode([
                    'access' => array_values($accessPermissions),
                    'modify' => array_values($modifyPermissions),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                $group->setName($name);
                $group->setPermission($permissionJson);

                $userGroupRepo->save($group);

                try {
                    $routeContext = RouteContext::fromRequest($request);
                    $url = $routeContext->getRouteParser()->urlFor('admin.user_group.list') . '?success=' . urlencode('Papel atualizado com sucesso!');
                } catch (\Throwable $e) {
                    $url = '/admin/papeis?success=' . urlencode('Papel atualizado com sucesso!');
                }

                return $response->withHeader('Location', $url)->withStatus(302);
            }
        }

        $permissions = $group->getPermissionArray();

        $html = $this->getTemplate('admin/user_group/user_group_form.html.twig', [
            'title'       => 'Editar Papel | Painel Administrativo',
            'modules'     => $this->modules,
            'user_group'  => [
                'id'         => $group->getId(),
                'name'       => $group->getName(),
                'permission' => [
                    'access' => $permissions['access'] ?? [],
                    'modify' => $permissions['modify'] ?? []
                ]
            ],
            'is_edit'     => true,
            'error'       => $error
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
