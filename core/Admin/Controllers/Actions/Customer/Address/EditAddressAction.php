<?php

namespace Alpha\Admin\Controllers\Actions\Customer\Address;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;
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

        /** @var CustomerAddressesRepository $addressRepo */
        $addressRepo = $this->getRepository(CustomerAddressesRepository::class);
        $address = $addressRepo->find($addressId);

        if (!$address || $address->getCustomerId() !== $customerId) {
            $response->getBody()->write('Endereço não encontrado ou não pertence a este cliente.');
            return $response->withStatus(404);
        }

        $dao = new DataAccessObject();
        $errors = [];

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $addressData = [
                'id'           => $addressId,
                'street'    => trim($data['street'] ?? ''),
                'number'       => (int)($data['number'] ?? 0),
                'complement'    => trim($data['complement'] ?? ''),
                'neighborhood' => trim($data['neighborhood'] ?? ''),
                'city'         => trim($data['city'] ?? ''),
                'postcode'     => trim($data['postcode'] ?? ''),
                'country_id'   => (int)($data['country_id'] ?? 76),
                'zone_id'      => (int)($data['zone_id'] ?? 0),
                'default'      => !empty($data['default']) ? 1 : 0
            ];

            // Validation rules
            if (strlen($addressData['postcode']) < 8) {
                $errors['postcode'] = 'O CEP deve ter pelo menos 8 caracteres.';
            }
            if (empty($addressData['street'])) {
                $errors['street'] = 'A rua/logradouro é obrigatória.';
            }
            if (empty($addressData['city'])) {
                $errors['city'] = 'A cidade é obrigatória.';
            }
            if ($addressData['number'] <= 0) {
                $errors['number'] = 'O número deve ser um valor inteiro maior que zero.';
            }
            if ($addressData['zone_id'] <= 0) {
                $errors['zone_id'] = 'Selecione um estado.';
            }

            if (empty($errors)) {
                try {
                    $addressRepo->save($addressData, $customerId);
                    $_SESSION['success'] = 'Endereço atualizado com sucesso!';
                    
                    return $response
                        ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/clientes/' . $customerId . '/editar?tab=addresses')
                        ->withStatus(302);
                } catch (\Throwable $e) {
                    $errors['warning'] = 'Erro ao atualizar endereço: ' . $e->getMessage();
                }
            }
        } else {
            $data = [
                'street'    => $address->getStreet(),
                'number'       => $address->getNumber(),
                'complement'    => $address->getComplement(),
                'neighborhood' => $address->getDistrict(),
                'city'         => $address->getCity() ? $address->getCity()->getName() : '',
                'postcode'     => $address->getPostalCode(),
                'country_id'   => $address->getCountry() ? $address->getCountry()->getId() : 76,
                'zone_id'      => $address->getZone() ? $address->getZone()->getId() : 0,
                'default'      => (int)$customer->getAddressId() === (int)$address->getId()
            ];
        }

        // Fetch active countries using GeoCountryMapper
        /** @var \Alpha\Mappers\EntityMappers\GeoCountryMapper $countryMapper */
        $countryMapper = $this->getMapper(\Alpha\Mappers\EntityMappers\GeoCountryMapper::class);
        $countries = array_map(fn($c) => [
            'id' => $c->getId(),
            'name' => $c->getName()
        ], $countryMapper->getCountries());

        // Fetch zones (Brazil)
        $zoneBuilder = (new QueryBuilder())
            ->from(DB_PREFIX . 'geo_zones', 'z')
            ->select('z.id', 'z.name', 'z.iso_code AS code')
            ->where('z.country_id = 76')
            ->orderBy('z.name', 'ASC');
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
