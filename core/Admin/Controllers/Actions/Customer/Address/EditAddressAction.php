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

class EditAddressAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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

        /** @var AddressRepository $addressRepo */
        $addressRepo = $this->getRepository(AddressRepository::class);
        $address = $addressRepo->find($addressId);

        if (!$address || $address->getCustomerId() !== $customerId) {
            $response->getBody()->write('Endereço não encontrado ou não pertence a este cliente.');
            return $response->withStatus(404);
        }

        /** @var CountryRepository $countryRepo */
        $countryRepo = $this->getRepository(CountryRepository::class);

        $dao = new DataAccessObject();
        $errors = [];

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $addressData = [
                'id'           => $addressId,
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
                    $_SESSION['success'] = 'Endereço atualizado com sucesso!';
                    
                    return $response
                        ->withHeader('Location', '/LPDHED2dC7Gjrg2b/clientes/' . $customerId . '/editar?tab=addresses')
                        ->withStatus(302);
                } catch (\Throwable $e) {
                    $errors['warning'] = 'Erro ao atualizar endereço: ' . $e->getMessage();
                }
            }
        } else {
            $data = [
                'firstname'    => $address->getFirstname(),
                'lastname'     => $address->getLastname(),
                'company'      => $address->getCompany(),
                'address_1'    => $address->getAddress1(),
                'number'       => $address->getNumber(),
                'address_2'    => $address->getAddress2(),
                'neighborhood' => $address->getNeighborhood(),
                'city'         => $address->getCity(),
                'postcode'     => $address->getPostcode(),
                'country_id'   => $address->getCountryId(),
                'zone_id'      => $address->getZoneId(),
                'default'      => $address->isDefault()
            ];
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

        $html = $this->getTemplate('admin/customer/Address/edit.html.twig', [
            'title'      => 'Editar Endereço | Painel Administrativo',
            'customer'   => $customer,
            'address'    => $address,
            'countries'  => $countries,
            'zones'      => $zones,
            'errors'     => $errors,
            'data'       => $data
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
