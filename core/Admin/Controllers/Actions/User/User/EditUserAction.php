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

class EditUserAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        /** @var UserRepository $userRepo */
        $userRepo = $this->getRepository(UserRepository::class);
        /** @var UserGroupRepository $userGroupRepo */
        $userGroupRepo = $this->getRepository(UserGroupRepository::class);

        /** @var User|null $user */
        $user = $userRepo->find($id);

        if (!$user) {
            try {
                $routeContext = RouteContext::fromRequest($request);
                $url = $routeContext->getRouteParser()->urlFor('admin.user.list') . '?error=' . urlencode('Funcionário não encontrado.');
            } catch (\Throwable $e) {
                $url = '/admin/usuarios?error=' . urlencode('Funcionário não encontrado.');
            }
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $error = null;
        $formData = [
            'id'            => $user->getId(),
            'username'      => $user->getUsername(),
            'firstname'     => $user->getFirstname(),
            'lastname'      => $user->getLastname(),
            'email'         => $user->getEmail(),
            'user_group_id' => $user->getUserGroupId(),
            'status'        => $user->isStatus() ? 1 : 0
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

            $existingUserByUsername = $userRepo->findByUsername($username);
            $existingUserByEmail = $userRepo->findByEmail($email);

            if (empty($username) || strlen($username) < 3) {
                $error = 'O nome de usuário (username) deve conter no mínimo 3 caracteres.';
            } elseif (empty($firstname) || empty($lastname)) {
                $error = 'Nome e sobrenome do funcionário são obrigatórios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Endereço de e-mail inválido.';
            } elseif ($existingUserByUsername && $existingUserByUsername->getId() !== $id) {
                $error = 'Atenção: Este nome de usuário já está em uso por outro perfil.';
            } elseif ($existingUserByEmail && $existingUserByEmail->getId() !== $id) {
                $error = 'Atenção: Este endereço de e-mail já está em uso por outro perfil.';
            } elseif (!empty($password) && (strlen($password) < 4 || $password !== $confirmPassword)) {
                $error = 'A senha deve conter no mínimo 4 caracteres e a confirmação deve ser idêntica.';
            } elseif ($userGroupId <= 0) {
                $error = 'Selecione um papel/grupo de usuário válido para o funcionário.';
            } else {
                $user->setUsername($username)
                     ->setFirstname($firstname)
                     ->setLastname($lastname)
                     ->setEmail($email)
                     ->setUserGroupId($userGroupId)
                     ->setStatus($status);

                if (!empty($password)) {
                    $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
                }

                $userRepo->save($user);

                try {
                    $routeContext = RouteContext::fromRequest($request);
                    $url = $routeContext->getRouteParser()->urlFor('admin.user.list') . '?success=' . urlencode('Funcionário atualizado com sucesso!');
                } catch (\Throwable $e) {
                    $url = '/admin/usuarios?success=' . urlencode('Funcionário atualizado com sucesso!');
                }

                return $response->withHeader('Location', $url)->withStatus(302);
            }
        }

        $userGroups = $userGroupRepo->findAll();

        $html = $this->getTemplate('admin/user/user_form.html.twig', [
            'title'       => 'Editar Funcionário | Painel Administrativo',
            'user_groups' => $userGroups,
            'user'        => $formData,
            'is_edit'     => true,
            'error'       => $error
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
