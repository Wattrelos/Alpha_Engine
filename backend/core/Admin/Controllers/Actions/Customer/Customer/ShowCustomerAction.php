<?php

namespace Alpha\Admin\Controllers\Actions\Customer\Customer;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;
use Alpha\Model\Domain\Repositories\CustomerGroupRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ShowCustomerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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
        $customerEntity = $customerRepo->find($customerId);

        if (!$customerEntity) {
            $response->getBody()->write('Cliente não encontrado.');
            return $response->withStatus(404);
        }

        /** @var CustomerGroupRepository $groupRepo */
        $groupRepo = $this->getRepository(CustomerGroupRepository::class);
        $group = $groupRepo->getCustomerGroup((int)$customerEntity->getCustomerGroupId(), $this->languageId);
        $groupName = $group['name'] ?? 'Padrão';

        $customerData = [
            'id'             => $customerEntity->getId(),
            'firstname'      => $customerEntity->getFirstname(),
            'lastname'       => $customerEntity->getLastname(),
            'email'          => $customerEntity->getEmail(),
            'telephone'      => $customerEntity->getTelephone(),
            'customer_group' => $groupName,
            'status'         => $customerEntity->getStatus(),
            'persontype'     => $customerEntity->getPersontype(),
            'cpf_cnpj'       => $customerEntity->getCpfCnpj(),
            'date_added'     => $customerEntity->getDateAdded()
        ];

        // 2. Fetch addresses
        /** @var CustomerAddressesRepository $addressRepo */
        $addressRepo = $this->getRepository(CustomerAddressesRepository::class);
        $addresses = $addressRepo->getAddresses($customerId);

        // 3. Fetch recent orders
        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->getRepository(OrderRepository::class);
        $ordersData = $orderRepo->getOrders($customerId, 0, 5);

        $orders = [];
        foreach ($ordersData as $o) {
            $orders[] = [
                'order_id'   => $o['order_id'] ?? $o['id'] ?? 0,
                'total'      => 'R$ ' . number_format((float)($o['total'] ?? 0), 2, ',', '.'),
                'date_added' => !empty($o['date_added']) ? date('d/m/Y H:i', strtotime($o['date_added'])) : '',
                'status'     => $o['status'] ?? 'Pendente'
            ];
        }

        $html = $this->getTemplate('admin/customer/customer/show.html.twig', [
            'title'     => 'Detalhes do Cliente | Painel Administrativo',
            'customer'  => $customerData,
            'addresses' => $addresses,
            'orders'    => $orders
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

