<?php

namespace Alpha\Admin\Controllers\Actions\User\UserGroup;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\UserGroupRepository;
use Alpha\Model\Domain\Repositories\LanguageRepository;
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
        /** @var LanguageRepository $languageRepo */
        $languageRepo = $this->getRepository(LanguageRepository::class);

        /** @var UserGroup|null $group */
        $group = $userGroupRepo->find($id);

        if (!$group) {
            try {
                $routeContext = RouteContext::fromRequest($request);
                $url = $routeContext->getRouteParser()->urlFor('admin.user_group.list') . '?error=' . urlencode('Papel não encontrado.');
            } catch (\Throwable $e) {
                $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';
                $url = $adminPath . '/papeis?error=' . urlencode('Papel não encontrado.');
            }
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $languages = $languageRepo->findAll();
        $error = null;

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody() ?? [];
            $names = $parsedBody['name'] ?? [];
            $accessPermissions = $parsedBody['permission']['access'] ?? [];
            $modifyPermissions = $parsedBody['permission']['modify'] ?? [];

            if (!is_array($names)) {
                $singleName = trim((string)$names);
                $names = [];
                foreach ($languages as $lang) {
                    $names[$lang->getId()] = $singleName;
                }
            }

            $firstName = '';
            foreach ($names as $n) {
                if (!empty(trim((string)$n))) {
                    $firstName = trim((string)$n);
                    break;
                }
            }

            if (empty($firstName)) {
                $error = 'O nome do papel é obrigatório.';
            } else {
                $permissionJson = json_encode([
                    'access' => array_values($accessPermissions),
                    'modify' => array_values($modifyPermissions),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                $group->setName($firstName);
                $group->setPermission($permissionJson);
                $group->setDescriptions($names);

                $userGroupRepo->save($group);

                try {
                    $routeContext = RouteContext::fromRequest($request);
                    $url = $routeContext->getRouteParser()->urlFor('admin.user_group.list') . '?success=' . urlencode('Papel atualizado com sucesso!');
                } catch (\Throwable $e) {
                    $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';
                    $url = $adminPath . '/papeis?success=' . urlencode('Papel atualizado com sucesso!');
                }

                return $response->withHeader('Location', $url)->withStatus(302);
            }
        }

        $permissions = $group->getPermissionArray();
        $descriptions = $userGroupRepo->findDescriptions($id);

        $html = $this->getTemplate('admin/user_group/user_group_form.html.twig', [
            'title'       => 'Editar Papel | Painel Administrativo',
            'modules'     => $this->modules,
            'languages'   => $languages,
            'user_group'  => [
                'id'           => $group->getId(),
                'name'         => $group->getName(),
                'descriptions' => $descriptions,
                'permission'   => [
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
