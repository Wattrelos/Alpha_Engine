<?php

namespace Alpha\Admin\Controllers\Actions\Customer\Address;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;

class DeleteAddressAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customerId = (int)($args['customer_id'] ?? 0);
        $addressId = (int)($args['id'] ?? 0);

        /** @var CustomerRepository $customerRepo */
        $customerRepo = $this->getRepository(CustomerRepository::class);
        $customer = $customerRepo->find($customerId);

        if (!$customer) {
            $response->getBody()->write('Cliente não encontrado.');
            return $response->withStatus(404);
        }

        /** @var CustomerAddressesRepository $addressRepo */
        $addressRepo = $this->getRepository(CustomerAddressesRepository::class);
        
        $deleteErrors = $addressRepo->validateDelete($customerId, $addressId);

        if (!empty($deleteErrors)) {
            $_SESSION['error'] = $deleteErrors['warning'] ?? 'Não foi possível excluir o endereço.';
        } else {
            try {
                $addressRepo->delete($addressId, $customerId);
                $_SESSION['success'] = 'Endereço excluído com sucesso!';
            } catch (\Throwable $e) {
                $_SESSION['error'] = 'Erro ao excluir endereço: ' . $e->getMessage();
            }
        }

        return $response
            ->withHeader('Location', '/LPDHED2dC7Gjrg2b/clientes/' . $customerId . '/editar?tab=addresses')
            ->withStatus(302);
    }
}
