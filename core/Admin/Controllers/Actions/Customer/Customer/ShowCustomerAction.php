<?php

namespace Alpha\Admin\Controllers\Actions\Customer\Customer;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Repositories\AddressRepository;

class ShowCustomerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customerId = (int)($args['id'] ?? 0);

        if (!$customerId) {
            $response->getBody()->write('ID do cliente não fornecido.');
            return $response->withStatus(400);
        }

        $dao = new DataAccessObject();

        // 1. Fetch customer details
        $customerBuilder = (new QueryBuilder())
            ->from(DB_PREFIX . 'customer', 'c')
            ->select('c.*', 'cgd.name AS customer_group')
            ->leftJoin(DB_PREFIX . 'customer_group_description', 'cgd', 'c.customer_group_id = cgd.customer_group_id AND cgd.language_id = ' . (int)$this->languageId)
            ->where('c.id = ?', [$customerId]);

        $customerData = $dao->executeQuery($customerBuilder);
        $customer = $customerData[0] ?? null;

        if (!$customer) {
            $response->getBody()->write('Cliente não encontrado.');
            return $response->withStatus(404);
        }

        // 2. Fetch addresses
        /** @var AddressRepository $addressRepo */
        $addressRepo = $this->getRepository(AddressRepository::class);
        $addresses = $addressRepo->getAddresses($customerId);

        // 3. Fetch recent orders
        $ordersBuilder = (new QueryBuilder())
            ->from(DB_PREFIX . 'order', 'o')
            ->leftJoin(DB_PREFIX . 'order_status', 'os', 'o.order_status_id = os.id AND os.language_id = ' . (int)$this->languageId)
            ->select('o.id', 'o.total', 'o.date_added', 'os.name AS status')
            ->where('o.customer_id = ?', [$customerId])
            ->orderBy('o.date_added', 'DESC')
            ->limit(5);

        $ordersData = $dao->executeQuery($ordersBuilder);

        $orders = [];
        foreach ($ordersData as $o) {
            $orders[] = [
                'order_id'   => $o['id'],
                'total'      => 'R$ ' . number_format((float)$o['total'], 2, ',', '.'),
                'date_added' => date('d/m/Y H:i', strtotime($o['date_added'])),
                'status'     => $o['status'] ?? 'Pendente'
            ];
        }

        $html = $this->getTemplate('admin/customer/customer/show.html.twig', [
            'title'     => 'Detalhes do Cliente | Painel Administrativo',
            'customer'  => $customer,
            'addresses' => $addresses,
            'orders'    => $orders
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
