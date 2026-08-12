<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\POS;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Action responsável por buscar clientes ativos no sistema para o PDV (POS).
 */
class SearchCustomerAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $query = $queryParams['q'] ?? '';

        /** @var CustomerRepository $customerRepo */
        $customerRepo = $this->getRepository(CustomerRepository::class);
        $customersRaw = $customerRepo->searchActiveCustomers($query, 15);

        $customers = [];
        foreach ($customersRaw as $c) {
            $customers[] = [
                'customer_id' => (int)$c['id'],
                'name'        => $c['firstname'] . ' ' . $c['lastname'],
                'email'       => $c['email'],
                'telephone'   => $c['telephone'],
            ];
        }

        $response->getBody()->write(json_encode($customers, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}

