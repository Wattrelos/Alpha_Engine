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

class StoreSupplierAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
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
            $html = $this->getTemplate('admin/catalog/supplier/create.html.twig', [
                'title'     => 'Adicionar Fornecedor | Painel Administrativo',
                'errors'    => $errors,
                'data'      => $data,
                'countries' => $countries
            ]);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        try {
            // Instancia as entidades
            $supplier = new Supplier();
            $supplier->setCompanyName($companyName);
            $supplier->setTradeName(trim($data['trade_name'] ?? ''));
            $supplier->setTaxId($taxId);
            $supplier->setStateRegistration(trim($data['state_registration'] ?? ''));
            $supplier->setMunicipalRegistration(trim($data['municipal_registration'] ?? ''));
            $supplier->setEmail($email);
            $supplier->setPhone(trim($data['phone'] ?? ''));
            $supplier->setWebsite(trim($data['website'] ?? ''));
            $supplier->setIsActive(isset($data['is_active']) && $data['is_active'] == '1');
            $supplier->setCreatedAt(date('Y-m-d H:i:s'));
            $supplier->setUpdatedAt(date('Y-m-d H:i:s'));

            $address = new Addresses();
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

            /** @var SupplierRepository $supplierRepository */
            $supplierRepository = RepositoryFactory::getInstance()->get(SupplierRepository::class);
            $supplierRepository->save($supplier);

        } catch (\Throwable $e) {
            $countries = $mapperFactory->get(GeoCountryMapper::class)->getCountries();
            $html = $this->getTemplate('admin/catalog/supplier/create.html.twig', [
                'title'     => 'Adicionar Fornecedor | Painel Administrativo',
                'errors'    => ['warning' => 'Erro ao salvar fornecedor: ' . $e->getMessage()],
                'data'      => $data,
                'countries' => $countries
            ]);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        return $response
            ->withHeader('Location', '/LPDHED2dC7Gjrg2b/fornecedores')
            ->withStatus(302);
    }
}
