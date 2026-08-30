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

class CreateUserGroupAction extends BaseController implements ActionInterface
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
        'system/audit'          => 'Auditoria & Logs de Acesso',
    ];

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var UserGroupRepository $userGroupRepo */
        $userGroupRepo = $this->getRepository(UserGroupRepository::class);
        /** @var LanguageRepository $languageRepo */
        $languageRepo = $this->getRepository(LanguageRepository::class);
        
        $languages = $languageRepo->findAll();
        $error = null;

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody() ?? [];
            $names = $parsedBody['name'] ?? [];
            $accessPermissions = $parsedBody['permission']['access'] ?? [];
            $modifyPermissions = $parsedBody['permission']['modify'] ?? [];

            // Se name for string única (fallback)
            if (!is_array($names)) {
                $singleName = trim((string)$names);
                $names = [];
                foreach ($languages as $lang) {
                    $names[$lang->getId()] = $singleName;
                }
            }

            // Pega um nome genérico para a coluna legada name em user_group
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

                $userGroup = new UserGroup();
                $userGroup->setName($firstName);
                $userGroup->setPermission($permissionJson);
                $userGroup->setDescriptions($names);

                $userGroupRepo->save($userGroup);

                try {
                    $routeContext = RouteContext::fromRequest($request);
                    $url = $routeContext->getRouteParser()->urlFor('admin.user_group.list') . '?success=' . urlencode('Papel criado com sucesso!');
                } catch (\Throwable $e) {
                    $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';
                    $url = $adminPath . '/papeis?success=' . urlencode('Papel criado com sucesso!');
                }

                return $response->withHeader('Location', $url)->withStatus(302);
            }
        }

        $html = $this->getTemplate('admin/user_group/user_group_form.html.twig', [
            'title'       => 'Novo Papel | Painel Administrativo',
            'modules'     => $this->modules,
            'languages'   => $languages,
            'user_group'  => [
                'name'         => '',
                'descriptions' => [],
                'permission'   => ['access' => [], 'modify' => []]
            ],
            'is_edit'     => false,
            'error'       => $error
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
