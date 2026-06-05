<?php

namespace Alpha\Admin\Controllers\Actions\Customer\Address;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Repositories\AddressRepository;
use Alpha\Model\Domain\Repositories\CountryRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;

class CreateAddressAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customerId = (int)($args['customer_id'] ?? 0);

        /** @var CustomerRepository $customerRepo */
        $customerRepo = $this->getRepository(CustomerRepository::class);
        $customer = $customerRepo->find($customerId);

        if (!$customer) {
            $response->getBody()->write('Cliente não encontrado.');
            return $response->withStatus(404);
        }

        /** @var AddressRepository $addressRepo */
        $addressRepo = $this->getRepository(AddressRepository::class);
        /** @var CountryRepository $countryRepo */
        $countryRepo = $this->getRepository(CountryRepository::class);

        $dao = new DataAccessObject();
        $errors = [];
        $data = [];

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $addressData = [
                'firstname'    => trim($data['firstname'] ?? ''),
                'lastname'     => trim($data['lastname'] ?? ''),
                'company'      => trim($data['company'] ?? ''),
                'address_1'    => trim($data['address_1'] ?? ''),
                'number'       => (int)($data['number'] ?? 0),
                'address_2'    => trim($data['address_2'] ?? ''),
                'neighborhood' => trim($data['neighborhood'] ?? ''),
                'city'         => trim($data['city'] ?? ''),
                'postcode'     => trim($data['postcode'] ?? ''),
                'country_id'   => (int)($data['country_id'] ?? 30),
                'zone_id'      => (int)($data['zone_id'] ?? 0),
                'default'      => !empty($data['default']) ? 1 : 0
            ];

            $errors = $addressRepo->validate($addressData);

            if ($addressData['number'] <= 0) {
                $errors['number'] = 'O número deve ser um valor inteiro maior que zero.';
            }

            if (empty($errors)) {
                try {
                    $addressRepo->save($addressData, $customerId);
                    $_SESSION['success'] = 'Endereço cadastrado com sucesso!';
                    
                    return $response
                        ->withHeader('Location', '/LPDHED2dC7Gjrg2b/clientes/' . $customerId . '/editar?tab=addresses')
                        ->withStatus(302);
                } catch (\Throwable $e) {
                    $errors['warning'] = 'Erro ao cadastrar endereço: ' . $e->getMessage();
                }
            }
        }

        // Fetch countries
        $countries = $countryRepo->getCountries();

        // Fetch zones (Brazil)
        $zoneBuilder = (new QueryBuilder())
            ->from(DB_PREFIX . 'zone', 'z')
            ->join(DB_PREFIX . 'zone_description', 'zd', 'z.id = zd.zone_id')
            ->select('z.id', 'zd.name', 'z.code')
            ->where('z.country_id = 30')
            ->where('zd.language_id = ?', [$this->languageId])
            ->orderBy('zd.name', 'ASC');
        $zones = $dao->executeQuery($zoneBuilder);

        $html = $this->getTemplate('admin/customer/Address/create.html.twig', [
            'title'      => 'Adicionar Endereço | Painel Administrativo',
            'customer'   => $customer,
            'countries'  => $countries,
            'zones'      => $zones,
            'errors'     => $errors,
            'data'       => $data
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
