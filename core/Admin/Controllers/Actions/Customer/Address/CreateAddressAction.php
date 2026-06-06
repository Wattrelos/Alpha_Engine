<?php

namespace Alpha\Admin\Controllers\Actions\Customer\Address;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;
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

        /** @var CustomerAddressesRepository $addressRepo */
        $addressRepo = $this->getRepository(CustomerAddressesRepository::class);

        $dao = new DataAccessObject();
        $errors = [];
        $data = [];

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $addressData = [
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
                    $_SESSION['success'] = 'Endereço cadastrado com sucesso!';
                    
                    return $response
                        ->withHeader('Location', '/LPDHED2dC7Gjrg2b/clientes/' . $customerId . '/editar?tab=addresses')
                        ->withStatus(302);
                } catch (\Throwable $e) {
                    $errors['warning'] = 'Erro ao cadastrar endereço: ' . $e->getMessage();
                }
            }
        }

        // Fetch active countries using GeoCountryMapper
        /** @var \Alpha\Mappers\EntityMappers\GeoCountryMapper $countryMapper */
        $countryMapper = $addressRepo->mapperFactory->get(\Alpha\Mappers\EntityMappers\GeoCountryMapper::class);
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
