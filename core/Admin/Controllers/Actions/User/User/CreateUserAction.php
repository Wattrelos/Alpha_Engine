<?php

namespace Alpha\Admin\Controllers\Actions\User\User;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\UserRepository;
use Alpha\Model\Domain\Repositories\UserGroupRepository;
use Alpha\Model\Domain\Entities\User;
use Slim\Routing\RouteContext;

class CreateUserAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getRepository(UserRepository::class);
        /** @var UserGroupRepository $userGroupRepo */
        $userGroupRepo = $this->getRepository(UserGroupRepository::class);

        $error = null;
        $formData = [
            'username'      => '',
            'firstname'     => '',
            'lastname'      => '',
            'email'         => '',
            'user_group_id' => 0,
            'status'        => 1
        ];

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody() ?? [];
            $formData = array_merge($formData, $parsedBody);

            $username = trim($parsedBody['username'] ?? '');
            $firstname = trim($parsedBody['firstname'] ?? '');
            $lastname = trim($parsedBody['lastname'] ?? '');
            $email = trim($parsedBody['email'] ?? '');
            $password = $parsedBody['password'] ?? '';
            $confirmPassword = $parsedBody['confirm_password'] ?? '';
            $userGroupId = (int)($parsedBody['user_group_id'] ?? 0);
            $status = isset($parsedBody['status']) ? (bool)$parsedBody['status'] : true;

            // Validações
            if (empty($username) || strlen($username) < 3) {
                $error = 'O nome de usuário (username) deve conter no mínimo 3 caracteres.';
            } elseif (empty($firstname) || empty($lastname)) {
                $error = 'Nome e sobrenome do funcionário são obrigatórios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Endereço de e-mail inválido.';
            } elseif ($userRepo->findByUsername($username)) {
                $error = 'Atenção: Este nome de usuário já está em uso.';
            } elseif ($userRepo->findByEmail($email)) {
                $error = 'Atenção: Este endereço de e-mail já está cadastrado.';
            } elseif (empty($password) || strlen($password) < 4) {
                $error = 'A senha deve conter no mínimo 4 caracteres.';
            } elseif ($password !== $confirmPassword) {
                $error = 'A confirmação da senha não coincide com a senha digitada.';
            } elseif ($userGroupId <= 0) {
                $error = 'Selecione um papel/grupo de usuário válido para o funcionário.';
            } else {
                $user = new User();
                $user->setUsername($username)
                     ->setFirstname($firstname)
                     ->setLastname($lastname)
                     ->setEmail($email)
                     ->setPassword(password_hash($password, PASSWORD_DEFAULT))
                     ->setUserGroupId($userGroupId)
                     ->setStatus($status)
                     ->setDateAdded(date('Y-m-d H:i:s'));

                $userRepo->save($user);

                try {
                    $routeContext = RouteContext::fromRequest($request);
                    $url = $routeContext->getRouteParser()->urlFor('admin.user.list') . '?success=' . urlencode('Funcionário cadastrado com sucesso!');
                } catch (\Throwable $e) {
                    $url = '/admin/usuarios?success=' . urlencode('Funcionário cadastrado com sucesso!');
                }

                return $response->withHeader('Location', $url)->withStatus(302);
            }
        }

        $userGroups = $userGroupRepo->findAll();

        $html = $this->getTemplate('admin/user/user_form.html.twig', [
            'title'       => 'Novo Funcionário | Painel Administrativo',
            'user_groups' => $userGroups,
            'user'        => $formData,
            'is_edit'     => false,
            'error'       => $error
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
