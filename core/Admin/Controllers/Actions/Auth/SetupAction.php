<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\UserRepository;
use Alpha\Model\Domain\Repositories\UserGroupRepository;
use Alpha\Model\Domain\Entities\User;
use Alpha\Model\Domain\Entities\UserGroup;

/**
 * SetupAction - Processa a criação do primeiro administrador (OOBE).
 */
class SetupAction implements ActionInterface
{
    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $userRepo = RepositoryFactory::getInstance()->get(UserRepository::class);
        $users = $userRepo->findAll();

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $loginUrl = $routeParser->urlFor('admin.login.form');

        if (count($users) > 0) {
            // OOBE bloqueado se já existirem usuários
            return $response->withHeader('Location', $loginUrl)->withStatus(302);
        }

        $post = $request->getParsedBody();
        $firstname = trim($post['firstname'] ?? '');
        $lastname = trim($post['lastname'] ?? '');
        $email = trim($post['email'] ?? '');
        $username = trim($post['username'] ?? '');
        $password = $post['password'] ?? '';
        $confirmPassword = $post['confirm_password'] ?? '';

        $errors = [];

        if (strlen($firstname) < 1) {
            $errors['firstname'] = 'O nome é obrigatório.';
        }
        if (strlen($lastname) < 1) {
            $errors['lastname'] = 'O sobrenome é obrigatório.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Informe um endereço de e-mail válido.';
        }
        if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'O usuário deve ter pelo menos 3 caracteres alfanuméricos.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'A senha deve ter pelo menos 6 caracteres.';
        }
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'As senhas informadas não coincidem.';
        }

        if ($errors) {
            $html = $this->twig->render('admin/auth/setup.html.twig', [
                'action' => $routeParser->urlFor('admin.setup.submit'),
                'errors' => $errors,
                'data'   => $post,
            ]);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        // Garante a existência do Grupo Administrator (ID 1) com permissões completas
        $userGroupRepo = RepositoryFactory::getInstance()->get(UserGroupRepository::class);
        /** @var UserGroup|null $group */
        $group = $userGroupRepo->find(1);

        if (!$group) {
            $group = new UserGroup();
            $group->setId(1);
            $group->setName('Administrator');
            
            // Permissões completas padrão
            $allPermissions = [
                'access' => [
                    'catalog/category', 'catalog/product', 'catalog/manufacturer',
                    'procurement/supplier', 'customer/customer', 'setting/setting', 'sale/order'
                ],
                'modify' => [
                    'catalog/category', 'catalog/product', 'catalog/manufacturer',
                    'procurement/supplier', 'customer/customer', 'setting/setting', 'sale/order'
                ]
            ];
            $group->setPermission(json_encode($allPermissions));
            $userGroupRepo->getMapper()->save($group);
        }

        // Cria o primeiro usuário administrador
        $user = new User();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $user->setUserGroupId(1);
        $user->setStatus(true);
        $user->setDateAdded(date('Y-m-d H:i:s'));

        $userRepo->getMapper()->save($user);

        // Redireciona para login com mensagem de sucesso
        $redirectUrl = $loginUrl . '?success=' . urlencode('Administrador configurado com sucesso! Agora você pode realizar o login.');
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }
}
