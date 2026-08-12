<?php

namespace Alpha\Admin\Controllers\Actions\Customer\Customer;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;
use Alpha\Model\Domain\Repositories\CustomerGroupRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EditCustomerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customerId = (int)($args['id'] ?? 0);

        if (!$customerId) {
            $response->getBody()->write('ID do cliente não fornecido.');
            return $response->withStatus(400);
        }

        /** @var CustomerRepository $customerRepo */
        $customerRepo = $this->getRepository(CustomerRepository::class);
        /** @var CustomerAddressesRepository $addressRepo */
        $addressRepo = $this->getRepository(CustomerAddressesRepository::class);

        // Carrega dados do cliente usando repositório (ORM Entity)
        $customerEntity = $customerRepo->find($customerId);

        if (!$customerEntity) {
            $response->getBody()->write('Cliente não encontrado.');
            return $response->withStatus(404);
        }

        $errors = [];

        // Process POST requests
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            // Lógica de Salvar Perfil do Cliente
            $firstname = trim($data['firstname'] ?? '');
            $lastname = trim($data['lastname'] ?? '');
            $email = trim($data['email'] ?? '');
            $telephone = trim($data['telephone'] ?? '');
            $customerGroupId = (int)($data['customer_group_id'] ?? 1);
            $status = isset($data['status']) ? (int)$data['status'] : 1;
            $safe = isset($data['safe']) ? (int)$data['safe'] : 0;
            $commenter = isset($data['commenter']) ? (int)$data['commenter'] : 0;
            $cpfCnpj = trim($data['cpf_cnpj'] ?? '');
            $persontype = trim($data['persontype'] ?? 'F');
            $password = $data['password'] ?? '';
            $confirm = $data['confirm'] ?? '';

            if (mb_strlen($firstname) < 1 || mb_strlen($firstname) > 32) {
                $errors['firstname'] = 'O nome deve ter entre 1 e 32 caracteres.';
            }

            if (mb_strlen($lastname) < 1 || mb_strlen($lastname) > 32) {
                $errors['lastname'] = 'O sobrenome deve ter entre 1 e 32 caracteres.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'O e-mail informado não é válido.';
            } else {
                // Verifica duplicidade de email usando repositório
                $existing = $customerRepo->findByEmail($email);
                if ($existing && $existing->getId() !== $customerId) {
                    $errors['email'] = 'Atenção: Este e-mail já está cadastrado.';
                }
            }

            if (mb_strlen($telephone) < 3 || mb_strlen($telephone) > 32) {
                $errors['telephone'] = 'O telefone deve ter entre 3 e 32 caracteres.';
            }

            if (!empty($password)) {
                if (mb_strlen($password) < 4) {
                    $errors['password'] = 'A senha deve conter pelo menos 4 caracteres.';
                }
                if ($password !== $confirm) {
                    $errors['confirm'] = 'A confirmação de senha não coincide com a senha.';
                }
            }

            if ($persontype === 'F' && !empty($cpfCnpj)) {
                $cpf = preg_replace('/[^0-9]/', '', $cpfCnpj);
                if (strlen($cpf) != 11) {
                    $errors['cpf_cnpj'] = 'O CPF informado deve conter 11 dígitos.';
                }
            } elseif ($persontype === 'J' && !empty($cpfCnpj)) {
                $cnpj = preg_replace('/[^0-9]/', '', $cpfCnpj);
                if (strlen($cnpj) != 14) {
                    $errors['cpf_cnpj'] = 'O CNPJ informado deve conter 14 dígitos.';
                }
            }

            if (empty($errors)) {
                try {
                    $customerEntity->setFirstname($firstname)
                        ->setLastname($lastname)
                        ->setEmail($email)
                        ->setTelephone($telephone)
                        ->setCustomerGroupId($customerGroupId)
                        ->setStatus((bool)$status)
                        ->setSafe((bool)$safe)
                        ->setCommenter((bool)$commenter)
                        ->setCpfCnpj($cpfCnpj)
                        ->setPersontype($persontype);

                    if (!empty($password)) {
                        $customerEntity->setPassword(password_hash($password, PASSWORD_DEFAULT));
                    }

                    $customerRepo->updateProfile($customerEntity);

                    $_SESSION['success'] = 'Cliente atualizado com sucesso!';

                    return $response
                        ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/clientes')
                        ->withStatus(302);

                } catch (\Throwable $e) {
                    $errors['warning'] = 'Erro ao atualizar cliente: ' . $e->getMessage();
                }
            }
        }

        // Carrega grupos de clientes usando o repositório
        /** @var CustomerGroupRepository $groupRepo */
        $groupRepo = $this->getRepository(CustomerGroupRepository::class);
        $customerGroupsRaw = $groupRepo->getCustomerGroups($this->languageId);

        $customerGroups = [];
        foreach ($customerGroupsRaw as $cg) {
            $customerGroups[] = [
                'id'   => $cg['customer_group_id'] ?? $cg['id'] ?? 0,
                'name' => $cg['name'] ?? ''
            ];
        }

        // Carrega endereços
        $addresses = $addressRepo->getAddresses($customerId);

        $success = $_SESSION['success'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['success'], $_SESSION['error']);

        $tab = $request->getQueryParams()['tab'] ?? 'general';

        $html = $this->getTemplate('admin/customer/customer/edit.html.twig', [
            'title'           => 'Editar Cliente | Painel Administrativo',
            'customer'        => $customerEntity, // Envia objeto de Entidade Rica para o Twig
            'customer_groups' => $customerGroups,
            'addresses'       => $addresses,
            'success'         => $success,
            'error'           => $error,
            'errors'          => $errors,
            'tab'             => $tab
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

