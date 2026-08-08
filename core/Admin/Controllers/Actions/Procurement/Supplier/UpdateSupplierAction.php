<?php

namespace Alpha\Admin\Controllers\Actions\Procurement\Supplier;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Entities\Supplier\Supplier;
use Alpha\Model\Domain\Entities\Supplier\Addresses;
use Alpha\Mappers\MapperFactory;
use Alpha\Mappers\EntityMappers\GeoCountryMapper;
use Alpha\Mappers\EntityMappers\GeoZoneMapper;
use Alpha\Mappers\EntityMappers\GeoCityMapper;
use Alpha\Model\Domain\Repositories\SupplierRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

class UpdateSupplierAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $supplierId = (int)($args['id'] ?? 0);

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = RepositoryFactory::getInstance()->get(SupplierRepository::class);
        $supplier = $supplierRepository->find($supplierId);

        if (!$supplier) {
            $response->getBody()->write('Fornecedor não encontrado.');
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();
        $errors = [];

        // Validation
        $companyName = trim($data['company_name'] ?? '');
        $taxId = preg_replace('/\D/', '', $data['tax_id'] ?? '');
        $email = trim($data['email'] ?? '');

        if (empty($companyName)) {
            $errors['company_name'] = 'A Razão Social é obrigatória.';
        }
        if (strlen($taxId) !== 14) {
            $errors['tax_id'] = 'O CNPJ deve conter exatamente 14 dígitos.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Informe um e-mail válido.';
        }

        // Endereço Validation
        $postalCode = preg_replace('/\D/', '', $data['postal_code'] ?? '');
        $street = trim($data['street'] ?? '');
        $number = trim($data['number'] ?? '');
        $countryId = (int)($data['country_id'] ?? 0);
        $zoneId = (int)($data['zone_id'] ?? 0);
        $cityId = (int)($data['city_id'] ?? 0);

        if (empty($postalCode)) {
            $errors['postal_code'] = 'O CEP é obrigatório.';
        }
        if (empty($street)) {
            $errors['street'] = 'A rua/logradouro é obrigatória.';
        }
        if (empty($number)) {
            $errors['number'] = 'O número é obrigatório.';
        }
        if (!$countryId) {
            $errors['country_id'] = 'Selecione o País.';
        }
        if (!$zoneId) {
            $errors['zone_id'] = 'Selecione o Estado.';
        }
        if (!$cityId) {
            $errors['city_id'] = 'Selecione a Cidade.';
        }

        $mapperFactory = MapperFactory::getInstance();

        if (!empty($errors)) {
            $countries = $mapperFactory->get(GeoCountryMapper::class)->getCountries();
            $zones = [];
            $cities = [];
            
            $address = $supplier->getAddresses();
            if ($address) {
                $country = $address->getCountry();
                if ($country) {
                    $zones = $mapperFactory->get(GeoZoneMapper::class)->getZonesByCountryId($country->getId());
                }
                $zone = $address->getZone();
                if ($zone) {
                    $cities = $mapperFactory->get(GeoCityMapper::class)->getCitiesByZoneId($zone->getId());
                }
            }

            /** @var \Alpha\Model\Domain\Repositories\ManufacturerRepository $manufacturerRepo */
            $manufacturerRepo = $this->getRepository(\Alpha\Model\Domain\Repositories\ManufacturerRepository::class);
            $manufacturers = $manufacturerRepo->getManufacturers();

            // Temporarily set contacts from parsed body so they are returned to form if validation fails
            $contactsData = isset($data['contacts']) && is_array($data['contacts']) ? $data['contacts'] : [];
            $supplier->setContacts($contactsData);

            $html = $this->getTemplate('admin/catalog/supplier/edit.html.twig', [
                'title'         => 'Editar Fornecedor | Painel Administrativo',
                'errors'        => $errors,
                'supplier'      => $supplier,
                'address'       => $address,
                'countries'     => $countries,
                'zones'         => $zones,
                'cities'        => $cities,
                'manufacturers' => $manufacturers
            ]);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        try {
            // Atualiza fornecedor
            $supplier->setCompanyName($companyName);
            $supplier->setTradeName(trim($data['trade_name'] ?? ''));
            $supplier->setTaxId($taxId);
            $supplier->setStateRegistration(trim($data['state_registration'] ?? ''));
            $supplier->setMunicipalRegistration(trim($data['municipal_registration'] ?? ''));
            $supplier->setEmail($email);
            $supplier->setPhone(trim($data['phone'] ?? ''));
            $supplier->setWebsite(trim($data['website'] ?? ''));
            $supplier->setIsActive(isset($data['is_active']) && $data['is_active'] == '1');
            $supplier->setUpdatedAt(date('Y-m-d H:i:s'));

            // Parse and set contacts
            $contactsData = isset($data['contacts']) && is_array($data['contacts']) ? $data['contacts'] : [];
            $supplier->setContacts($contactsData);

            // Obtém ou instancia endereço
            $address = $supplier->getAddresses();
            if (!$address) {
                $address = new Addresses();
            }
            
            $address->setPostalCode($postalCode);
            $address->setStreet($street);
            $address->setNumber($number);
            $address->setComplement(trim($data['complement'] ?? ''));
            $address->setDistrict(trim($data['district'] ?? ''));

            // Associações
            $country = $mapperFactory->get(GeoCountryMapper::class)->findById($countryId);
            $zone = $mapperFactory->get(GeoZoneMapper::class)->findById($zoneId);
            $city = $mapperFactory->get(GeoCityMapper::class)->findById($cityId);

            $address->setCountry($country);
            $address->setZone($zone);
            $address->setCity($city);

            $supplier->setAddresses($address);

            $supplierRepository->save($supplier);

        } catch (\Throwable $e) {
            $countries = $mapperFactory->get(GeoCountryMapper::class)->getCountries();
            $zones = [];
            $cities = [];
            
            $address = $supplier->getAddresses();
            if ($address) {
                $country = $address->getCountry();
                if ($country) {
                    $zones = $mapperFactory->get(GeoZoneMapper::class)->getZonesByCountryId($country->getId());
                }
                $zone = $address->getZone();
                if ($zone) {
                    $cities = $mapperFactory->get(GeoCityMapper::class)->getCitiesByZoneId($zone->getId());
                }
            }

            /** @var \Alpha\Model\Domain\Repositories\ManufacturerRepository $manufacturerRepo */
            $manufacturerRepo = $this->getRepository(\Alpha\Model\Domain\Repositories\ManufacturerRepository::class);
            $manufacturers = $manufacturerRepo->getManufacturers();

            // Temporarily set contacts back to Supplier entity in case of error
            $contactsData = isset($data['contacts']) && is_array($data['contacts']) ? $data['contacts'] : [];
            $supplier->setContacts($contactsData);

            $html = $this->getTemplate('admin/catalog/supplier/edit.html.twig', [
                'title'         => 'Editar Fornecedor | Painel Administrativo',
                'errors'        => ['warning' => 'Erro ao atualizar fornecedor: ' . $e->getMessage()],
                'supplier'      => $supplier,
                'address'       => $address,
                'countries'     => $countries,
                'zones'         => $zones,
                'cities'        => $cities,
                'manufacturers' => $manufacturers
            ]);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        return $response
            ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/fornecedores')
            ->withStatus(302);
    }
}
